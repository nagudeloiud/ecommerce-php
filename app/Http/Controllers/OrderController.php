<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\log;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Arr;
use Illuminate\Support\Facades\DB;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderProduct;



class OrderController extends Controller
{
    public function create(Request $request)
    {
                
        try { 
            DB::beginTransaction();
              
        $request->validate([
            "email" => 'required|email'

        ]);


        // Crear una orden
        $order = new Order();
        $order->email = $request->email;
        $order->saveOrFail();

        $idOrder = $order->id;
 
       //$products = $this->obtenerProductos();
 
        // Recorrer productos
        $products = $request->input('products');
        if(count($products) <=0 ){
            return response()->json(['message' => 'Debe ingresar toda la información del producto']);
        }
 
        //ingresar los datos a la tabla orderProduct
        for($i=0;$i<count($products);$i++){
        
        $productoActualizado = Product::find($products[$i]["id"]);
            
        $producto= new OrderProduct();
        $producto->order_id=$idOrder;
        $producto->product_id=$products[$i]["id"];
        $producto->price=$productoActualizado->price;
        $producto->quantity=$products[$i]["quantity"];
                
        if ($producto->quantity > $productoActualizado->inventory OR $producto->quantity <=0) {
            
            return response()->json(['message' => 'Verifique la cantidad ingresada']);
        
        }
        $producto->save();
        //Actualizar el inventario
        $productoActualizado->inventory -= $producto->quantity;
        $productoActualizado->save();
        
    } 
       
        DB::commit();

        return response()->json(['message' => 'Datos ingresados con exito']);

        }

        catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error, Verifique la información que ingresó']);
        }
          return response()->json(['message' => 'Success']);

    
    }


    public function index() {
        $orders = Order::with(['products'])->get();
        return response()->json($orders, 200);
    }


    public function getById($email) {
        $orders = Order::with(['products'])
                            ->where('email', $email)    
                            ->first();

        if (empty($orders)) {
            return response()->json(['message' => 'Not Found'], 404);
        }      

        return response()->json($orders, 200);
    }  

}
  