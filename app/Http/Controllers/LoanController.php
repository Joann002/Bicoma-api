<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoanRequest;
use App\Http\Resources\LoanResource;
use App\Models\Item;
use App\Models\Loan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LoanController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $loans = $this->scopedQuery($request)
            ->with('item')
            ->when($request->boolean('active'), fn ($q) => $q->where('returned', false))
            ->latest('loan_date')
            ->get();

        return LoanResource::collection($loans);
    }

    public function overdue(Request $request): AnonymousResourceCollection
    {
        $loans = $this->scopedQuery($request)
            ->with('item')
            ->where('returned', false)
            ->whereNotNull('return_date')
            ->whereDate('return_date', '<', now())
            ->orderBy('return_date')
            ->get();

        return LoanResource::collection($loans);
    }

    public function store(StoreLoanRequest $request): JsonResponse
    {
        $item = $request->user()->items()->findOrFail($request->integer('item_id'));

        $loan = $item->loans()->create([
            'borrower_name' => $request->string('borrower_name'),
            'loan_date' => $request->date('loan_date') ?? now(),
            'return_date' => $request->date('return_date'),
            'returned' => false,
        ]);

        return LoanResource::make($loan->load('item'))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, Loan $loan): LoanResource
    {
        $this->authorizeLoan($request, $loan);

        $data = $request->validate([
            'borrower_name' => ['sometimes', 'string', 'max:255'],
            'loan_date' => ['sometimes', 'date'],
            'return_date' => ['nullable', 'date'],
        ]);

        $loan->update($data);

        return LoanResource::make($loan->load('item'));
    }

    public function markReturned(Request $request, Loan $loan): LoanResource
    {
        $this->authorizeLoan($request, $loan);

        $loan->update([
            'returned' => true,
            'returned_at' => now(),
        ]);

        return LoanResource::make($loan->load('item'));
    }

    public function destroy(Request $request, Loan $loan): \Illuminate\Http\Response
    {
        $this->authorizeLoan($request, $loan);

        $loan->delete();

        return response()->noContent();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Loan>
     */
    private function scopedQuery(Request $request)
    {
        return Loan::query()->whereHas('item', fn ($q) => $q->where('user_id', $request->user()->id));
    }

    private function authorizeLoan(Request $request, Loan $loan): void
    {
        abort_unless(
            Item::where('id', $loan->item_id)->where('user_id', $request->user()->id)->exists(),
            403
        );
    }
}
