<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDish;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Metodo per creare un ordine
    public function createOrder(Request $request)
    {
        // Valida la richiesta
        $request->validate([
            'nonce' => 'required|string', 
            'amount' => 'required|numeric', 
            'cart' => 'required|array',
            'customer_name' => 'required|string|max:120',
            'customer_surname' => 'required|string|max:120',
            'shipping_address' => 'required|string|max:120',
            'total_items' => 'required|integer',
        ]);

        // Crea un nuovo ordine
        $order = new Order();
        $order->customer_name = $request->input('customer_name');
        $order->customer_surname = $request->input('customer_surname');
        $order->shipping_address = $request->input('shipping_address');
        $order->total_price = $request->input('amount');
        $order->total_items = $request->input('total_items');
        $order->save();

        // Aggiungi i piatti all'ordine
        foreach ($request->input('cart') as $dish) {
            OrderDish::create([
                'order_id' => $order->id,
                'dish_id' => $dish['id'], 
                'quantity' => $dish['quantity'], 
            ]);
        }

        return response()->json([
            'success' => true,
            'orderId' => $order->id,
        ]);
    }

    // Nuovo metodo saveOrder
    public function saveOrder(Request $request)
    {
        // Valida la richiesta
        $request->validate([
            'customer_name' => 'required|string|max:120',
            'customer_surname' => 'required|string|max:120',
            'shipping_address' => 'required|string|max:120',
            'amount' => 'required|numeric', 
            'cart' => 'required|array', // Assicurati che la richiesta contenga i piatti
            'total_items' => 'required|integer',
        ]);

        // Crea l'ordine
        $order = new Order();
        $order->customer_name = $request->input('customer_name');
        $order->customer_surname = $request->input('customer_surname');
        $order->shipping_address = $request->input('shipping_address');
        $order->total_price = $request->input('amount');
        $order->total_items = $request->input('total_items');
        $order->status = 'pending'; // Puoi iniziare con lo stato 'pending'
        $order->save();

        // Aggiungi i piatti all'ordine
        foreach ($request->input('cart') as $dish) {
            OrderDish::create([
                'order_id' => $order->id,
                'dish_id' => $dish['id'],
                'quantity' => $dish['quantity'], 
            ]);
        }

        // Restituisci la risposta con l'ID dell'ordine
        return response()->json([
            'success' => true,
            'orderId' => $order->id,
        ], 201);
    }

    // Metodo per ottenere gli ordini
    public function getOrders()
    {
        $orders = Order::with('orderDishes.dish')->get();

        $totalOrders = $orders->count();
        $totalRevenue = $orders->sum('total_price');

        return response()->json([
            'orders' => $orders,
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
        ]);
    }

    // Metodo per visualizzare gli ordini di un ristorante
    public function showOrders($restaurantId)
    {
        // Assicurati che il ristoratore abbia accesso agli ordini di questo ristorante
        $restaurant = Restaurant::findOrFail($restaurantId);
    
        // Ottieni gli ordini relativi ai piatti di questo ristorante
        $orders = Order::whereHas('orderDishes.dish.restaurant', function($query) use ($restaurantId) {
            $query->where('restaurant_id', $restaurantId);
        })
        ->with('orderDishes.dish')  // Include i piatti associati agli ordini
        ->get();
    
        // Restituisci la vista con gli ordini
        return view('admin.orders.index', compact('restaurant', 'orders'));
    }
}
