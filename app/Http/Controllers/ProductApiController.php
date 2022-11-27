<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\log;

use App\Models\Product;

class ProductApiController extends Controller
{
    public function __construct()  
    {
        //$this->middleware('auth:api')->only(['getById']);
        //$this->middleware(['client.credentials'])->only(['index']);
    }

    
    public function store(Request $request){
        
    
        
        log::info(print_r($request->input(),true));
        $product = Product::create([
            "name" => $request->input("name"),
            //Nombre de la base de datos y nombre del json
            "description" => $request->input("description"),
            "price" => $request->input("price"),
            "inventory" => $request->input("inventory"),
            "category_id" => $request->input("category"),
            "brand_id" => $request->input("brand"),
            "seller_id" => $request->input("seller"),
            "image" => $request->input("image"),

        ]);

        return response()->json($product, 201);
    }


    public function create(Request $request){
       // log::info(print_r($request->input(),true));
        $data = $request->json()->all();

        $categorys = $data['category'];

        log::info(print_r($categorys,true));

        $product = Product::create([
            "name" => $data["name"],
            "description" => $data["description"],
            "price" => $data["price"],
            "inventory" => $data["inventory"],
            "category_id" => $categorys["id"],
            "brand_id" => $data["brand"],
            "seller_id" => $data["seller"],
            "image" => $data["image"], 
        ]);

        $product->category->create([
            //"id" => $categorys["id"],
            "name" => $categorys["name"],
        ]);

        

         return response()->json($product, 201);

        }

      
        
    

    
    
    public function index() {
        $products = Product::with(['Category', 'Brand', 'Seller'])->get();
        return response()->json($products, 200);
    }

    public function getById($id) {
        $product = Product::with(['Category', 'Brand', 'Seller'])
                            ->where('id', $id)    
                            ->first();

        if (empty($product)) {
            return response()->json(['message' => 'Not Found'], 404);
        }      

        return response()->json($product, 200);
    }
}
