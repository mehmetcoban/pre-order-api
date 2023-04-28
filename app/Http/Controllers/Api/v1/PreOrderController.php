<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\PreOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PreOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin')->only(['update', 'index']);
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $preOrders = PreOrder::all();

        foreach ($preOrders as $preOrder) {
            $preOrder->products;
        }

        return response()->json($preOrders);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $preOrder = PreOrder::firtOrFail($id);

        if (!$preOrder) {
            return response()->json(['error' => 'Pre order not found.'], 404);
        }

        return response()->json($preOrder);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            //'phone' => 'required|required|regex:/^\+90\d{10}$/|min:13|max:13', // For tr
            'phone' => 'required|required|regex:/^\+1\d{10}$/|min:10',
            'email' => 'required|email',
        ], [
            'phone.regex' => 'Please enter the phone number in the format with +90 at the beginning.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $validatedData = $validator->validate();

        $user = Auth::user();
        $cart = $user->cart;

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 404);
        }

        $preOrder = new PreOrder;
        $preOrder->user_id = $user->id;
        $preOrder->first_name = $validatedData['first_name'];
        $preOrder->last_name = $validatedData['last_name'];
        $preOrder->email = $validatedData['email'];
        $preOrder->phone = $validatedData['phone'];
        $preOrder->save();

        foreach ($cart->items as $cartItem) {
            $preOrder->products()->attach($cartItem->product_id, ['quantity' => $cartItem->quantity]);
        }

        $cart->items()->delete();
        $cart->delete();

        return response()->json(['message' => 'Pre-order has been created.'], 201);
    }

    /**
     * @param Request $request
     * @param PreOrder $preOrder
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, PreOrder $preOrder): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:waiting,approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $preOrder->update([
            'status' => $request->get('status')
        ]);

        return response()->json($preOrder);
    }
}
