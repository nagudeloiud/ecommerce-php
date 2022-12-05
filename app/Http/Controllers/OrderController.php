<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderProduct;
use App\Rules\HasStock;
use Exception;

class OrderController extends Controller
{
    public function index(){
        return phpinfo();
    }

    public function getByEmail($email){
        $ordenes = Order::where("email", $email)->get();
        return response()->json([$ordenes], 200);        
    }

    public function create(Request $request)
    {      
        $validator = Validator::make($request->all(), [
            "email" => ["required", "email"],
            "products" => ["required"],
            "products.*.id" => ["required", "integer", "min:1", "exists:products", new TieneInventario],
            "products.*.quantity" => ["required", "integer", "min:1"]
        ]);       
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $data = request()->all();
        $nueva_orden = Order::create([
            "email" => $data["email"]
        ]);
        $total = 0.0;
        $detalles = [];
        foreach ($data["products"] as $item){
            $product = Product::findOrFail($item["id"]);
            $nuevo_po = OrderProduct::create([
                "order_id" => $nueva_orden["id"],
                "product_id" => $product["id"],
                "price" => $product["price"],
                "quantity" => $item["quantity"],
                "total" => $item["quantity"] * $product["price"]
            ]);
            $product->inventory -= $item["quantity"];
            $product->save();
            $detalles[] = ["ID" => $product->id, "Name" => $product->name, "Quantity" => $item["quantity"], "Unit price" => $product["price"], "Subtotal" => $nuevo_po["total"]];
            $total += $nuevo_po["total"];
        }
        $nueva_orden->total = $total;
        $nueva_orden->save();
        return response()->json(["Pedido" => $nueva_orden, "Detalle" => $detalles], 200);        
    }
}
