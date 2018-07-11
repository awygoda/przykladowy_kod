<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminProducts;
use Illuminate\Support\Facades\DB;
use App\Product;
use App\Manufacturer;
use App\Collection;
use App\Tax;
Use App\Attribute;
Use App\AttributeValue;
Use App\AttributeProduct;
Use App\AttributeProductAttributeValue;
Use App\RecommendedProduct;
use Session;

class AdminProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    
    public function index()
    {
        $products = Product::with('manufacturer',
                                    'collection', 
                                    'productPhotos',
                                    'firstCategories', 
                                    'secondCategories',
                                    'thirdCategories',
                                    'fourthCategories',
                                    'tax',
                                    'attributes',
                                    'recommendedProducts',
                                    'attributes.values.attributeValue'
                                    )->get();

        return view('admin.products', compact('products'));
    }

    public function create()
    {
        $manufacturer = Manufacturer::pluck('name', 'id');
        $collection = Collection::pluck('name', 'id');
        $attribute = Attribute::with('attributeValues')->get();
        $products = Product::all();

        $taxes= Tax::all();
        return view('admin.products_create', compact('manufacturer', 'collection', 'taxes', 'attribute', 'attributeValue', 'products'));
    }

    public function store(AdminProducts $request)
    {
        // dd($request);
        $validated = $request->validated();
        try{
            DB::beginTransaction();

            $products = new Product($request->all());

            $products->sale = 0;
            if (!empty($request->gross_price_sale)) {
                $products->sale = 1;
                $products->sort_price = $request->gross_price_sale;
            }
            $products->sort_price = $request->gross_price;
            if (!empty($request->gross_price_sale)) {
                $products->sort_price =  $request->gross_price_sale;
            }

            if ($request->floor_panel) {
                $squareMeterGrossPrice = $request->gross_price / $request->square_meter_in_package;
                $products->square_meter_gross_price = $squareMeterGrossPrice;
                $products->sort_price = $squareMeterGrossPrice;
                if (!empty($request->gross_price_sale)) {
                    $squareMeterGrossPriceSale = $request->gross_price_sale / $request->square_meter_in_package;
                    $products->sort_price = $squareMeterGrossPriceSale;
                    $products->square_meter_gross_price_sale = $squareMeterGrossPriceSale;
                }
            }


            $products->manufacturer_id = null;
            $products->collection_id = null;
            if ($request->manufacturer_id != 'noManufacturer') {
                $products->manufacturer_id = $request->manufacturer_id;
            }
            if ($request->collection_id != 'noCollection') {
                $products->collection_id = $request->collection_id;
            }
            $products->status = 1;
            $cenzura = array('ą', 'ć', 'ł', 'ó', 'ś', ' ', 'ę', 'ń', 'ż', 'ź', 'Ą', 'Ć', 'Ł', 'Ó', 'Ś', 'Ę', 'Ń', 'Ż', 'Ź' );
            $zamiana = array('a', 'c', 'l', 'o', 's', '-', 'e', 'n', 'z', 'z', 'A', 'C', 'L', 'O', 'S', 'E', 'N', 'Z', 'Z' );
            $link=strtolower(str_replace( $cenzura, $zamiana, $request->name));
            $products->link = $link;
            $products->save($request->all());
            $id = $products->id;

            $recommendedProduct = new RecommendedProduct();
            if($request->recommendedItem1 != 0) {
                $recommendedProduct->product_id = $id;
                $recommendedProduct->recommended_product_id = $request->recommendedItem1;
                $recommendedProduct->save();
            }

            $recommendedProduct = new RecommendedProduct();
            if($request->recommendedItem2 != 0) {
                $recommendedProduct->product_id = $id;
                $recommendedProduct->recommended_product_id = $request->recommendedItem2;
                $recommendedProduct->save();
            }

            $recommendedProduct = new RecommendedProduct();
            if($request->recommendedItem3 != 0) {
                $recommendedProduct->product_id = $id;
                $recommendedProduct->recommended_product_id = $request->recommendedItem3;
                $recommendedProduct->save();
            }

            if ($request->has('attribute_id')) {
                for ($i = 0; $i != count($request->attribute_id); $i++) {
                    $attributeProduct = new AttributeProduct();
                    $attributeProduct->product_id = $id;
                    $attributeProduct->attribute_id = $request->attribute_id[$i];
                    $attributeProduct->timestamps = false;
                    $attributeProduct->save();
                    $idAttributeProduct = $attributeProduct->id;
                    for ($j = 0; $j != count($request->value); $j++) {
                        if ($i == $j) {
                            $attributeProductValue = new AttributeProductAttributeValue();
                            $attributeProductValue->attribute_product_id = $idAttributeProduct;
                            $attributeProductValue->attribute_value_id = $request->value[$j];
                            $attributeProductValue->timestamps = false;
                            $attributeProductValue->save();
                        }
                    }
                }
            }

            if (json_decode($request->categories) != null) {
                foreach (json_decode($request->categories, true) as $v) {
                    $table = explode('-', $v);
                    if ($table[0] == 1) {
                        DB::table('first_category_product')->insert(
                            ['first_category_id' => $table[1], 'product_id' => $id]
                        );
                    } elseif ($table[0] == 2) {
                        DB::table('product_second_category')->insert(
                            ['second_category_id' => $table[1], 'product_id' => $id]
                        );
                    } elseif ($table[0] == 3) {
                        DB::table('product_third_category')->insert(
                            ['third_category_id' => $table[1], 'product_id' => $id]
                        );
                    } elseif ($table[0] == 4) {
                        DB::table('fourth_category_product')->insert(
                            ['fourth_category_id' => $table[1], 'product_id' => $id]
                        );
                    }
                }
            }

            DB::commit();
            if($request->save == 'Zapisz') {
                Session::flash('message', 'Produkt został dodany');
                return redirect('admin843157/products');
            } else {
                return redirect('admin843157/products/create-images/'.$id);
            }
        
        } catch(\Exception $e){
            DB::rollback();
            $errors = 'Błąd zapisu do bazy danych';
            return redirect()->back()->withErrors($errors);
        }
    }

    public function edit($id)
    {
        
        $product = Product::with('manufacturer',
                                    'collection', 
                                    'productPhotos',
                                    'firstCategories', 
                                    'secondCategories',
                                    'thirdCategories',
                                    'fourthCategories',
                                    'tax',
                                    'attributes',
                                    'recommendedProducts',
                                    'attributes.values.attributeValue')->findOrFail($id);
        $manufacturer = Manufacturer::pluck('name', 'id');
        $collection = Collection::pluck('name', 'id');
        $attribute = Attribute::with('attributeValues')->get();
        $products = Product::all();
        $taxes= Tax::all();
        return view('admin.products_edit', compact('products','manufacturer', 'collection', 'taxes', 'attribute', 'attributeValue', 'product'));
    }

    public function update($id, AdminProducts $request)
    {
        //  dd($request);
        $validated = $request->validated();

       
        try{
            DB::beginTransaction();
            $products = Product::findOrFail($id);
            $products->name = $request->name;
            $products->code_product = $request->code_product;
            $products->description = $request->description;
            $products->seo_description = $request->seo_description;
            $products->seo_keywords = $request->seo_keywords;
            $products->gross_price = $request->gross_price;
            $products->gross_price_sale = $request->gross_price_sale;
            $products->gross_price = $request->gross_price;
            $products->tax_id = $request->tax_id;
            $products->available_from_stock = 0;
            if ($request->available_from_stock) {   
                $products->available_from_stock = 1;
            }
            $products->warranty = $request->warranty;
            $products->dimensions = $request->dimensions;
            $products->weight = $request->weight;
            $products->psc_available = $request->psc_available;
            $products->shipment_in = 0;
            $products->shipment_in_time = null;
            $products->shipment_in_unit = 0;
            if ($request->shipment_in) {
                $products->shipment_in = 1;
                $products->shipment_in_time = $request->shipment_in_time;
                if ($request->shipment_in_unit) {
                    $products->shipment_in_unit = 1;
                }
            }
            $products->free_delivery = 0;
            $products->free_delivery_price_from = null;
            if ($request->free_delivery) {
                $products->free_delivery = 1;
                $products->free_delivery_price_from = $request->free_delivery_price_from;
            }

            $products->sale = 0;
            if (!empty($request->gross_price_sale)) {
                $products->sale = 1;
                $products->sort_price = $request->gross_price_sale;
            }
            $products->sort_price = $request->gross_price;
            if (!empty($request->gross_price_sale)) {
                $products->sort_price =  $request->gross_price_sale;
            }

            if ($request->floor_panel) {
                $products->floor_panel = 1;
                $products->psc_in_package = $request->psc_in_package;
                $products->square_meter_in_package = $request->square_meter_in_package;

                $squareMeterGrossPrice = $request->gross_price / $request->square_meter_in_package;
                $products->square_meter_gross_price = $squareMeterGrossPrice;
                $products->sort_price = $squareMeterGrossPrice;
                if (!empty($request->gross_price_sale)) {
                    $squareMeterGrossPriceSale = $request->gross_price_sale / $request->square_meter_in_package;
                    $products->sort_price = $squareMeterGrossPriceSale;
                    $products->square_meter_gross_price_sale = $squareMeterGrossPriceSale;
                }
            } else {
                $products->floor_panel = 0;
                $products->psc_in_package = null;
                $products->square_meter_in_package = null;
                $products->square_meter_gross_price = null;
                $products->square_meter_gross_price_sale = null;
            }
            $products->manufacturer_id = null;
            $products->collection_id = null;
            if ($request->manufacturer_id != 'noManufacturer') {
                $products->manufacturer_id = $request->manufacturer_id;
            }
            if ($request->collection_id != 'noCollection') {
                $products->collection_id = $request->collection_id;
            }

            $attributeProduct = AttributeProduct::where('product_id', '=', $id)->delete();
            if ($request->has('attribute_id')) {
                for ($i = 0; $i != count($request->attribute_id); $i++) {
                    $attributeProduct = new AttributeProduct();
                    $attributeProduct->product_id = $id;
                    $attributeProduct->attribute_id = $request->attribute_id[$i];
                    $attributeProduct->timestamps = false;
                    $attributeProduct->save();
                    $idAttributeProduct = $attributeProduct->id;
                    for ($j = 0; $j != count($request->value); $j++) {
                        if ($i == $j) {
                            $attributeProductValue = new AttributeProductAttributeValue();
                            $attributeProductValue->attribute_product_id = $idAttributeProduct;
                            $attributeProductValue->attribute_value_id = $request->value[$j];
                            $attributeProductValue->timestamps = false;
                            $attributeProductValue->save();
                        }
                    }
                }
            }

            $sql = DB::table('first_category_product')->where('product_id', '=',  $id)->delete();
            $sql = DB::table('product_second_category')->where('product_id', '=',  $id)->delete();
            $sql = DB::table('product_third_category')->where('product_id', '=',  $id)->delete();
            $sql = DB::table('fourth_category_product')->where('product_id', '=',  $id)->delete();
            if (json_decode($request->categories) != null) {
                        
                foreach (json_decode($request->categories, true) as $v) {
                    $table = explode('-', $v);
                    if ($table[0] == 1) {
                        DB::table('first_category_product')->insert(
                            ['first_category_id' => $table[1], 'product_id' => $id]
                        );
                    } elseif ($table[0] == 2) {
                        DB::table('product_second_category')->insert(
                            ['second_category_id' => $table[1], 'product_id' => $id]
                        );
                    } elseif ($table[0] == 3) {
                        DB::table('product_third_category')->insert(
                            ['third_category_id' => $table[1], 'product_id' => $id]
                        );
                    } elseif ($table[0] == 4) {
                        DB::table('fourth_category_product')->insert(
                            ['fourth_category_id' => $table[1], 'product_id' => $id]
                        );
                    }
                }
            }

            $recommendedProduct = RecommendedProduct::where('product_id', $id)->delete();
            $recommendedProduct = new RecommendedProduct();
            if($request->recommendedItem1 != 0) {
                $recommendedProduct->product_id = $id;
                $recommendedProduct->recommended_product_id = $request->recommendedItem1;
                $recommendedProduct->save();
            }

            $recommendedProduct = new RecommendedProduct();
            if($request->recommendedItem2 != 0) {
                $recommendedProduct->product_id = $id;
                $recommendedProduct->recommended_product_id = $request->recommendedItem2;
                $recommendedProduct->save();
            }

            $recommendedProduct = new RecommendedProduct();
            if($request->recommendedItem3 != 0) {
                $recommendedProduct->product_id = $id;
                $recommendedProduct->recommended_product_id = $request->recommendedItem3;
                $recommendedProduct->save();
            }
            $cenzura = array('ą', 'ć', 'ł', 'ó', 'ś', ' ', 'ę', 'ń', 'ż', 'ź', 'Ą', 'Ć', 'Ł', 'Ó', 'Ś', 'Ę', 'Ń', 'Ż', 'Ź' );
            $zamiana = array('a', 'c', 'l', 'o', 's', '-', 'e', 'n', 'z', 'z', 'A', 'C', 'L', 'O', 'S', 'E', 'N', 'Z', 'Z' );
            $link=strtolower(str_replace( $cenzura, $zamiana, $request->name));
            $products->link = $link;
            $products->save();
            DB::commit();
            Session::flash('message', 'Zmiany zostały zapisane.');
            return redirect('admin843157/products');
        } catch(\Exception $e){
            DB::rollback();
            $errors = 'Błąd zapisu do bazy danych';
            return redirect()->back()->withErrors($errors);
        }    
    }
}