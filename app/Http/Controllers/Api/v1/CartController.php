<?php

namespace App\Http\Controllers\Api\v1;

use App\Events\ProductQuantityUpdate;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        $user = auth()->user();

        $cart = $user->cart;

        if (!$cart) {
            return response()->json(['error' => 'Basket not found'], 404);
        }

        $cartItems = $cart->items;

        foreach ($cartItems as $cartItem) {
            $cartItem->product;
        }

        return response()->json($cartItems);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addToCart(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|int|exists:products,id',
            'quantity' => 'required|int|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = $request->user();
        $productId = $request->get('product_id');
        $quantity = $request->get('quantity');

        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $cart = $user->cart;

        if (!$cart) {
            $cart = new Cart();
            $cart->user_id = $user->id;
            $cart->save();
        }

        event(new ProductQuantityUpdate($product, $quantity));

        $existingCartItem = $cart->items()->where('product_id', $productId)->first();
        if ($existingCartItem) {
            $existingCartItem->quantity += $quantity;
            $existingCartItem->save();
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
        }

        return response()->json(['message' => 'Product added to cart successfully'], 201);
    }

    /**
     * @param Request $request
     * @param $productId
     * @return JsonResponse
     */
    public function updateQuantity(Request $request, $productId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|int|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = $request->user();
        $cart = $user->cart;

        $quantity = $request->get('quantity');

        $cartProduct = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->firstOrFail();

        $cartProduct->update([
            'quantity' => $quantity,
        ]);

        return response()->json(['message' => 'Cart updated successfully']);
    }

    /**
     * @param Request $request
     * @param $productId
     * @return JsonResponse
     */
    public function removeFromCart(Request $request, $productId): JsonResponse
    {
        $user = $request->user();

        $cart = $user->cart;

        $cartItem = $cart->items()->where('product_id', $productId)->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Product not found in cart'], 404);
        }

        $cartItem->delete();

        if ($cart->items()->count() === 0) {
            $cart->delete();
        }

        return response()->json(['message' => 'Product removed from cart'], 204);
    }

    /**
     * @return JsonResponse
     */
    public function destroy(): JsonResponse
    {
        $user = auth()->user();

        $cart = $user->cart;

        $cart->delete();
        $cart->items()->delete();

        return response()->json(['message' => 'Cart cleared successfully'], 204);
    }
}
