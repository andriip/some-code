<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Category2;
use App\Product_model;
use App\Product;
//use App\FrontProductController;
use App\Cab;
use App\Salon;
use App\Brands;
use App\Settings;
use App\Models;
use App\Banner;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use PhpParser\Node\Expr\AssignOp\Mod;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;

class FrontIndexController extends Controller
{

    public function __construct(){
        $Settings = Settings::select('option','value')->where('page', 'index')->get()->toArray();
        $settingArr = [];
        foreach($Settings as $set){
            $settingArr[$set['option']] = $set ['value'];
        }

        $cartTotal = \Cart::count() . ' товаров';
        $bannerMain = Banner::where('banner_type', 'indexMainBanner')->get();

        \View::share(['settings'=> $settingArr, 'cartTotal'=>$cartTotal, 'bannerMain' => $bannerMain]);
    }


    public function index()
    {

        $categories = Category2::where('parent_id', NULL)->get();
        $categoryArr=[];

            foreach($categories as $cat){
                $categoryArr[]=[
                        'name'  => $cat->name,
                        'href' => '/' . $cat->slug,
                        'img' => $cat->image,
                        'cntProduct' => $cat->getProductCnt(),
                        'cntManufacture' => $cat->getManCnt(),
                        'maxPrice' => $cat->getMaxPrice(),
                        'minPrice' => $cat->getMinPrice(),
                        ];
            }

        $count = Product::all()->count();
        $categories = Category2::where('parent_id', NULL)->get();
        $brands = Brands::all();
        $bannerMain = Banner::where('banner_type', 'indexMainBanner')->get();


//        $popular = Product::select('name')->where('pop_koef', '>', 8)->get()->toArray();
        $popular = Product::select('id', 'slug','name','image','price', 'manufacturer_id', 'category_id')->orderBy('pop_koef', 'desc')->take(10)->get();
//        dd($popular);

        return view('front.index', ['categories' => $categories, 'brands' => $brands, 'count' => $count,
            'bannerMain' => $bannerMain, 'categoryArr' => $categoryArr, 'popular'=>$popular]);
    }


    // public function postSearchZalupator(Request $request)
    // {
    //     $data = \Purifier::clean($request->except('_token'));
    //     dd($data);
    // }

    public function postSearch(Request $request)
    {

        $data = \Purifier::clean($request->except('_token'));
//        dd($data);
        if( !empty($data['search_tags']) ){
                //dd($data);
            return redirect('/' . 'searchTags' . '/' . $data['search_tags']);
        }

        if( (!isset($data['category2']) || $data['category2'] == "0") && (!isset($data['brand']) || $data['brand'] == "0")
            && (!isset($data['model']) || $data['model'] == "0") /*&& (!isset($data['cab']) || $data['cab'] == "0")
            && (!isset($data['year']) || $data['year'] == "0")*/ ) { //выбрана только категория - выводим список подкатегорий

            return redirect('/' . $data['category_slug']);

        } elseif ( (!isset($data['brand']) || $data['brand'] == "0")
            && (!isset($data['model']) || $data['model'] == "0")/* && (!isset($data['cab']) || $data['cab'] == "0")
            && (!isset($data['year']) || $data['year'] == "0") */) { //выбрана только категория и под категория
            return redirect('/' . $data['category_slug'] . '/' . $data['category2_slug']);
        }
        elseif ( (!isset($data['model']) || $data['model'] == "0") /*&& (!isset($data['cab']) || $data['cab'] == "0")
            && (!isset($data['year']) || $data['year'] == "0") */) { //выбрана только категория, под категория, бренд
            // dd('yup');
            $second_fragment = $data['category2_slug'] != "" ? $data['category2_slug'].'/' : '';
            return redirect('/' . $data['category_slug'] . '/' . $second_fragment . $data['brand_slug']);
        }
        else/*if ( (!isset($data['cab']) || $data['cab'] == "0") && (!isset($data['year']) || $data['year'] == "0") )*/ {
            //выбрана только категория, под категория, бренд и марка
            $models_ids = explode(',', $data['model']);
            $models = Models::select('slug')->whereIn('id', $models_ids)->get()->toArray();
            $models_slug = implode(';', array_flatten($models));
            $second_fragment = $data['category2_slug'] != "" ? $data['category2_slug'].'/' : '';
            $filters = (isset($data['cab']) && $data['cab'] != "0") ? ('?cab='.$data['cab'].((isset($data['year']) &&
                $data['year'] != "0") ? '&year='.$data['year'] : '')) : '';

            return redirect('/' . $data['category_slug'] . '/' . $second_fragment .$data['brand_slug'].'/'.
                $models_slug . $filters);
        }
        /*else {
            return redirect('/' . $data['category_slug'] . '/' . $data['category2_slug'].'/'.$data['brand_slug']);
        }*/
    }


    public function searchTags(Request $request, $tag){

        $p = Product::where('meta_tags', 'LIKE', '%'.$tag.'%')->paginate(20);

        return view('front.products_catalog', ['products' => $p, 'link' => true]);

    }


    public function brand_catalog(Request $request, $brand_slug, $model_slug=null, $param2=null, $param3=null, $param4=null)
    {
        //$bannerMain = Banner::where('banner_type', 'indexMainBanner')->get();

        //dd('zzz');
        //dd($brand_slug);



        if(Brands::where('brand_name', '=', $brand_slug)->exists()) {
            // $model_slug - слаг модели, выводим список категорий, в которых есть товары для этой модели
            $brand = Brands::where('brand_name', '=', $brand_slug)->first();

            //dd($brand);

            if(isset($model_slug)) {
                //заходит 2 параметр

                $model = Models::where('slug', '=', $model_slug)->first();
//                dd('zzz');
                if(isset($param2)) { // $param2 - слаг категории

                    if( isset($param3) ){ //слаг под категории
//                        dd('zzz');
                        if(isset($param4)){ //товар

                            $c = Category2::select('id')->where('slug', '=', $param3)->first();
                            $a = [0=>$model_slug];
                            return FrontProductController::slug($param4, $c->id, $a, $brand_slug);

                        }

                            if(Category2::select('id', 'name', 'meta_title')->where('slug', $param3)->exists()){
                                //если 4 параметром зашла категория
                                //sub category
                                $prods_by_model = Product_model::select('product_id')->where('model_id', '=', $model->id)
                                    ->get()->toArray();
                                $c = Category2::select('id', 'name', 'meta_title')->where('slug', $param3)->first();
                                $p = Product::whereIn('id' ,array_flatten($prods_by_model))
                                    ->where('category_id', $c->id)->paginate(30);

                                $brand=Brands::where('brand_name', '=', $brand_slug)->first();
                                $models=Models::where('slug', '=', $model_slug)->first();
                                $category_slug=Category2::where('slug', '=', $param2)->first();
                                $subcategory_slug=Category2::where('slug', '=', $param3)->first();
//dd($subcategory_slug);

                                return view('front.brands.products_catalog', ['products' => $p, 'brand'=> $brand,
                                    'models'=>$models, 'category_slug'=>$category_slug,  'category' => $c, 'subcategory_slug'=>$subcategory_slug]);
                            } else if (Product::select('id')->where('slug', $param3)->exists()){
                                //если 4 пареметром зашел существующий продукт

                                $c = Category2::select('id')->where('slug', '=', $param2)->first();
                                $a = [0=>$model_slug];

                                return FrontProductController::slug($param3, $c->id, $a, $brand_slug);

                            }

                    } else {

                        //если заходит первая категория
                        if(Category2::where('slug', '=', $param2)->whereNull('parent_id')->exists()) {
                            $category = Category2::where('slug', '=', $param2)->whereNull('parent_id')->first();
                            if($category->type == 1) {

                                // родительская категория, возвращаем список дочерних категорий в которых есть товары от этой модели

                                $prods_by_model = Product_model::select('product_id')->where('model_id', '=', $model->id)
                                    ->get()->toArray();
                                $subcats_for_cat = Category2::select('id')->where('parent_id', '=', $category->id)->get()->toArray();
                                $subcats_ids = Product::select('category_id')->whereIn('id', array_flatten($prods_by_model))
                                    ->get()->toArray();
                                $subcats = Category2::whereIn('id', array_flatten($subcats_for_cat))->whereIn('id',
                                    array_flatten($subcats_ids))->get();
                                //dd('$subcats');


                                $brand=Brands::where('brand_name', '=', $brand_slug)->first();
                                $models=Models::where('slug', '=', $model_slug)->first();
                                $category_slug=Category2::where('slug', '=', $param2)->first();
//                                dd($category_slug);
                                return view('front.brands.subcategory_catalog', ['subcategories' => $subcats,
                                    'model' => $model, 'brand'=>$brand, 'models'=>$models, 'category_slug'=>$category_slug]);
                            } elseif ($category->type == 2) {

                                //категория с товарами.... param2

                                $prods_by_model = Product_model::select('product_id')->where('model_id', '=', $model->id)
                                    ->get()->toArray();
                                $c = Category2::select('id')->where('slug', $param2)->first();

                                $p = Product::whereIn('id' ,array_flatten($prods_by_model))
                                    ->where('category_id', $c->id)->paginate(30);

                                $brand=Brands::where('brand_name', '=', $brand_slug)->first();
                                $models=Models::where('slug', '=', $model_slug)->first();
                                $category_slug=Category2::where('slug', '=', $param2)->first();
                                $subcategory_slug=Category2::where('slug', '=', $param3)->first();

                                return view('front.brands.products_catalog', ['products' => $p, 'subcategory_slug'=>$subcategory_slug,
                                    'category' => $c, 'brand'=>$brand, 'models'=>$models, 'category_slug'=>$category_slug]);


                            } elseif ($category->type == 3) {

                                //категория с моделями... предворительно ничем от с продуктами не отличается

                                $prods_by_model = Product_model::select('product_id')->where('model_id', '=', $model->id)
                                    ->get()->toArray();
                                $c = Category2::select('id')->where('slug', $param2)->first();

                                $p = Product::whereIn('id' ,array_flatten($prods_by_model))
                                    ->where('category_id', $c->id)->paginate(30);

                                $brand=Brands::where('brand_name', '=', $brand_slug)->first();
                                $models=Models::where('slug', '=', $model_slug)->first();
                                $category_slug=Category2::where('slug', '=', $param2)->first();
                                $subcategory_slug=Category2::where('slug', '=', $param3)->first();
//dd($brand);

                                return view('front.brands.products_catalog', ['products' => $p, 'brand'=>$brand, 'models'=>$models,
                                   'category_slug'=>$category_slug, 'subcategory_slug'=>$subcategory_slug, 'category' => $c]);

                                //dd('type3');

                            }
                        } else {
                            abort(404);
                        }
                    }



                }

                //только 2 параметр... вивод category.blade ...
                $products_models = Product_model::select('product_id')->where('model_id', '=', $model->id)
                    ->groupBy('product_id')->get()->toArray();

                $products = Product::select('category_id')->whereIn('id', array_flatten($products_models))
                    ->groupBy('category_id')->get()->toArray();


                $categories1 = Category2::whereIn('id', array_flatten($products))->whereNotNull('parent_id')->
                    groupBy('parent_id')->get()/*->toArray()*/;

                $categories2 = Category2::whereIn('id', array_flatten($products))->whereNull('parent_id')->get()/*->toArray()*/;

                 $cz = $categories1->merge($categories2);

                //dd($cz);

                $brand=Brands::where('brand_name', '=', $brand_slug)->first();
                $models=Models::where('slug', '=', $model_slug)->first();

                return view('front.brands.category_catalog', ['categories' => $cz,
                    'products_models' => $products_models, 'brand' =>$brand, 'models'=>$models]);

            } else {

                //только 1 параметр... вивод марок...
                $models_ids = Models::select('id')->where('brand_id', '=', $brand->id)->get()->toArray();

                $products_models = Product_model::select('model_id')->whereIn('model_id', array_flatten($models_ids))
                    ->groupBy('model_id')->get()->toArray();

                //dd($products_models);

                $models = Models::whereIn('id', array_flatten($products_models))->whereIn('id', array_flatten($models_ids))->get();

//                dd($brand_slug);

                $brand=Brands::where('brand_name', '=', $brand_slug)->first();
//dd($brand);
                return view('front.brands.models_catalog', ['models' => $models, 'products_models' => $products_models, 'brand'=>$brand]);

            }
        } else {
            abort(404);
        }
    }

    public function category_catalog(Request $request, $category_slug, $param1=null, $param2=null, $param3=null, $param4=null)
    {
        $bannerMain = Banner::where('banner_type', 'indexMainBanner')->get();

        $category = null;
        if(Category2::where('slug', '=', $category_slug)->whereNull('parent_id')->exists()) {
            $category = Category2::where('slug', '=', $category_slug)->whereNull('parent_id')->first();
        } else {
            abort(404);
        }
        if($category->type == 1) { //родительская категория
            //$param1 - дочерняя категория
            $subcategory = null;
            if(isset($param1)) {
                if(Category2::where('slug', '=', $param1)->whereNotNull('parent_id')->exists()) {
                    $subcategory = Category2::where('slug', '=', $param1)->whereNotNull('parent_id')->first();
                    if($subcategory->type == 2) { // с товарами. Выводим список товаров для этой подкатегории
                        if(isset($param2)){
                            return FrontProductController::slug($param2, $subcategory->id, null, null);
                        } else {
                            $products = Product::where('category_id', '=', $subcategory->id)->paginate(30);
                            return view('front.products_catalog', ['products' => $products, 'category' => $category,
                                'bannerMain' => $bannerMain, 'subcategory' => $subcategory]);
                        }

                    } elseif ($subcategory->type == 3) { // с марками
                        $brand = null;
                        if(isset($param2)) { // $param2 - марка; РОУТ: категория/подкатегория-с-марками/слаг-марки. Выводим МОДЕЛИ
                            if(Brands::where('brand_name', '=', $param2)->exists()) {
                                if(isset($param3)) { // $param3 - модели, выводить список товаров по этим моделям и подкатегории
                                    $models_slug = explode(';', $param3);
                                    if(isset($param4)) { // $param4 - слаг товара, выводим товар, соблюдая цепочку урла
                                        // dd($param2);
                                        return FrontProductController::slug($param4, $subcategory->id, $models_slug, $param2);
                                    }
                                    $cab_type_id = $request->get('cab');
                                    $year = $request->get('year');
                                    if(isset($cab_type_id)) {
                                        if(Cab::where('id', '=', (int) $cab_type_id)->exists()) {
                                            if(isset($year)) {
                                                $models = Models::select('id')->whereIn('slug', $models_slug)->where('cab_id',
                                                    '=', (int) $cab_type_id)->where('start_production', '<=', (int) $year)
                                                    ->where(function($query) use ($year){
                                                         $query->where('end_production', '>=', (int) $year);
                                                         $query->orWhereNull('end_production');
                                                     })->get()->toArray();
                                            } else {
                                                $models = Models::select('id')->whereIn('slug', $models_slug)->where('cab_id', '=',
                                                    (int) $cab_type_id)->get()->toArray();
                                            }
                                        } else {
                                            abort(404);
                                        }
                                    } else {
                                        $models = Models::select('id')->whereIn('slug', $models_slug)->get()->toArray();
                                    }
                                    $get_params = '';
                                    if(isset($cab_type_id)) {
                                        $get_params = '?cab=' . $cab_type_id . (isset($year) ? '&year=' . $year : '');
                                    }
                                    // $models = Models::select('id')->whereIn('slug', $models_slug)->get()->toArray();
                                    $prods_ids = Product::select('id')->where('category_id', '=', $subcategory->id)
                                        ->get()->toArray();
                                    $products_models = Product_model::select('product_id')->whereIn('product_id',
                                        array_flatten($prods_ids))->whereIn('model_id', array_flatten($models))
                                        ->groupBy('product_id')->get()->toArray();
                                    $products = Product::whereIn('id', array_flatten($products_models))->paginate(30);
                                    $products->setPath(url()->current().$get_params);



                                    $brand = Brands::where('brand_name', '=', $param2)->first();
                                    $model_name = Models::where('slug', '=', $param3)->first();
                                    //dd($model_name);

                                    return view('front.products_catalog', ['products' => $products, 'category' => $category,
                                        'bannerMain' => $bannerMain, 'brand' => $brand, 'subcategory' => $subcategory,
                                        'model_name' => $model_name]);


                                } else {
                                    $brand = Brands::where('brand_name', '=', $param2)->first();
                                    $prods = Product::select('id')->where('category_id', '=', $subcategory->id)
                                        ->get()->toArray();
                                    $prods_models = Product_model::select('model_id')
                                        ->whereIn('product_id', array_flatten($prods))->get()->toArray();
                                    $models_names = Models::select('model_name')->whereIn('id', array_flatten($prods_models))
                                        ->where('brand_id', '=', $brand->id)->groupBy('model_name')->get()->toArray();
                                    $models_get = Models::whereIn('model_name', array_flatten($models_names))
                                        ->where('brand_id', '=', $brand->id)->paginate(30);

                                    return view('front.models_catalog', ['models' => $models_get, 'category' => $category,
                                        'bannerMain' => $bannerMain, 'subcategory' => $subcategory, 'brand' => $brand]);
                                }
                            } else {
                                abort(404);
                            }
                        } else { //РОУТ: категория/подкатегория-с-марками. Выводим МАРКИ
                            $products_ids = Product::select('id')->where('category_id', '=', $subcategory->id)->get()->toArray();
                            $models_ids = Product_model::select('model_id')->whereIn('product_id', array_flatten($products_ids))
                                ->get()->toArray();
                            $models = Models::select('brand_id')->whereIn('id', array_flatten($models_ids))->groupBy('brand_id')
                                ->get()->toArray();
                            $brands = Brands::whereIn('id', array_flatten($models))->get();

                            return view('front.brands_catalog', ['brands' => $brands, 'category' => $category,
                                'bannerMain' => $bannerMain, 'subcategory' => $subcategory]);
                        }
                    }
                } else {
                    abort(404);
                }
            }
            $subcztegories = $category->getImmediateDescendants();


            return view('front.category_catalog', ['subcztegories' => $subcztegories, 'category' => $category,
                'bannerMain' => $bannerMain]);

        } else { //дочерняя категория, определяем тип

            if($category->type == 2) { // с товарами
                if(isset($param2)) {
                    abort(404);
                } elseif (isset($param1)) { // $param1 - слаг товара
                    return FrontProductController::slug($param1, $category->id, null, null);
                }
                $products = Product::where('category_id', '=', $category->id)->paginate(30);
                return view('front.products_catalog', ['products' => $products, 'category' => $category,
                    'bannerMain' => $bannerMain]);
            } else { // с марками (брендами)

                $subcategory = null;
                if(isset($param1)) { // $param1 - бренд
                    if(Brands::where('brand_name', '=', $param1)->exists()) {
                        if(isset($param2)) { // $param2 - модель, выводим список товаров для этой модели и категории

                            if( isset($param3) ){

                                $models_slug = explode(';', $param2);
                                return FrontProductController::slug($param3, $category->id, $models_slug, $param1);

                            }

                            $models_slug = explode(';', $param2);
                            if(!Models::whereIn('slug', $models_slug)->exists()) {
                                abort(404);
                            }
                            $cab_type_id = $request->get('cab');
                            $year = $request->get('year');
                            if(isset($cab_type_id)) {
                                if(Cab::where('id', '=', (int) $cab_type_id)->exists()) {
                                    if(isset($year)) {
                                        $models = Models::select('id')->whereIn('slug', $models_slug)->where('cab_id',
                                            '=', (int) $cab_type_id)->where('start_production', '<=', (int) $year)
                                            ->where(function($query) use ($year){
                                                 $query->where('end_production', '>=', (int) $year);
                                                 $query->orWhereNull('end_production');
                                             })->get()->toArray();
                                    } else {
                                        $models = Models::select('id')->whereIn('slug', $models_slug)->where('cab_id', '=',
                                            (int) $cab_type_id)->get()->toArray();
                                    }
                                } else {
                                    abort(404);
                                }
                            } else {
                                $models = Models::select('id')->whereIn('slug', $models_slug)->get()->toArray();
                            }
                            //$brand = Brands::where('brand_name', '=', $param1)->first();
                            $get_params = '';
                            if(isset($cab_type_id)) {
                                $get_params = '?cab=' . $cab_type_id . (isset($year) ? '&year=' . $year : '');
                            }
                            $prods_ids = Product::select('id')->where('category_id', '=', $category->id)->get()->toArray();
                            $models_prods = Product_model::select('product_id')->whereIn('model_id', array_flatten($models))
                                ->groupBy('product_id')->get()->toArray();
                            $products = Product::whereIn('id', array_flatten($models_prods))->whereIn('id',
                                array_flatten($prods_ids))->where('category_id', '=', $category->id)->paginate(30);
                            $products->setPath(url()->current().$get_params);

                            return view('front.products_catalog', ['products' => $products, 'category' => $category,
                                'bannerMain' => $bannerMain]);

                        } else { // Возвращаем список моделей у которых есть товары в этой категории по бренду(марке)
                            $brand = Brands::where('brand_name', '=', $param1)->first();
                            $prods_ids = Product::select('id')->where('category_id', '=', $category->id)->get()->toArray();
                            $models_ids = Models::select('id')->where('brand_id', '=', $brand->id)->get()->toArray();
                            $models_prods = Product_model::select('model_id')->whereIn('model_id', array_flatten($models_ids))
                                ->whereIn('product_id', $prods_ids)->get()->toArray();
                            $models = Models::whereIn('id', $models_prods)->get();

                            //dd('zz');

                            return view('front.models_catalog', ['models' => $models, 'category' => $category, 'bannerMain'
                                => $bannerMain, 'subcategory' => $subcategory, 'brand' => $brand]);
                        }
                    } else {
                        abort(404);
                    }
                    $model = null;
                    if(isset($param2)) {
                        //dd($param2);
                    } else {
                        $products_ids = Product::select('id')->where('category_id', '=', $subcategory->id)->get()->toArray();
                        $models_ids = Product_model::select('model_id')->whereIn('product_id', array_flatten($products_ids))
                            ->get()->toArray();
                        $models = Models::select('brand_id')->whereIn('id', array_flatten($models_ids))->groupBy('brand_id')
                            ->get()->toArray();
                        $brands = Brands::whereIn('id', array_flatten($models))->get();
                        return view('front.brands_catalog', ['brands' => $brands, 'category' => $category,
                            'bannerMain' => $bannerMain, 'subcategory' => $subcategory]);
                    }
                } else { // тип категории - 3, выводим список брендов(марок)
                    $products_ids = Product::select('id')->where('category_id', '=', $category->id)->get()->toArray();
                    $models_ids = Product_model::select('model_id')->whereIn('product_id', array_flatten($products_ids))
                        ->groupBy('model_id')->get()->toArray();
                    $brands_ids = Models::select('brand_id')->whereIn('id', array_flatten($models_ids))->groupBy('brand_id')
                        ->get()->toArray();
                    $brands = Brands::whereIn('id', array_flatten($brands_ids))->get();
                    $subcategory = null;
                    return view('front.brands_catalog', ['brands' => $brands, 'category' => $category,
                        'bannerMain' => $bannerMain, 'subcategory' => $subcategory]);
                }
            }
        }
    }


    public function ajaxSearch(Request $request)
    {
//        dd('zzz');
        if($request->ajax()) {
            $data = \Purifier::clean($request->except('_token'));
            $action = $data['action'];
            switch ($action) {
                case 'category':
                    if(Category2::where('id', $data['category'])->exists()) {
                        $category = Category2::where('id', $data['category'])->first()->immediateDescendants();
                        $return = $category->select('id', 'name', 'slug', 'type')->get()->toArray();

                        if(count($return) == 0) {
                            $c = Category2::select('type')->where('id', $data['category'])->first()->toArray();
                            //dd($c);
                            $count = Product::where('category_id', $data['category'])->count();
                            return json_encode(['data' => [], 'count' => $count, 'type' =>$c['type']]);
                        } else {
                            $categories_ids = array_flatten(array_pluck($return, 'id'));
                            $count = Product::whereIn('category_id', $categories_ids)->count();
                            return json_encode(['data' => $return, 'brands' => [], 'count' => $count]);
                        }
                    } else {
                        return 'error';
                    }

                case 'category2':



                    if(Category2::where('id', $data['category'])->exists()) {
                        $count = Product::where('category_id', $data['category'])->count();
                        $type = Category2::select('type')->where('id', $data['category'])->first()->toArray();
                        //dd($type);
                        return json_encode(['data' => [], 'count' => $count, 'type' => $type['type']]);
                    } else {
                        return 'error';
                    }

                case 'brand':
                    if(Brands::where('id', $data['brand'])->exists()) {
                        $prods = Product::select('id')->where('category_id', '=', $data['category'])
                            ->get()->toArray();
                        $prods_models = Product_model::select('model_id')
                            ->whereIn('product_id', array_flatten($prods))->get()->toArray();
                        $models_names = Models::select('model_name')->whereIn('id', array_flatten($prods_models))
                            ->where('brand_id', '=', $data['brand'])->groupBy('model_name')->get()->toArray();
                        $models_array = [];
                        $models_get = Models::select('id', 'model_name')->whereIn('model_name', array_flatten($models_names))
                            ->where('brand_id', '=', $data['brand'])->get();
                        foreach($models_get as $mg) {
                            $models_array[$mg->model_name] = !isset($models_array[$mg->model_name]) ? $mg->id.',' :
                                $models_array[$mg->model_name] . $mg->id.',';
                        }
                        $models_ids = array_flatten(array_pluck($models_get->toArray(), 'id'));
                        $prods = array_flatten(array_pluck($prods, 'id'));
                        $count = \DB::table('products_model')->select('product_id')->whereIn('model_id', $models_ids)
                            ->whereIn('product_id', $prods)->groupBy('product_id')->get();
                        return json_encode(['data' => $models_array, 'count' => count($count)]);
                    } else {
                        return 'error';
                    }

                case 'model':



                    $arr = explode(',', $data['models']);

                    //dd($arr);

                    if(Models::whereIn('id', $arr)->exists()) {
                        $models = Models::select('cab_id')->whereIn('id', $arr)->where('brand_id', $data['brand'])
                            ->get()->toArray();
                        $cabs = Cab::select('cab_name', 'id')->whereIn('id', array_flatten($models))->get()->toArray();

                        $models_cabs = array_flatten(array_pluck($cabs, 'id'));
                        $prods_models = \DB::table('products_model')->select('product_id')->whereIn('model_id', $arr)
                            ->groupBy('product_id')->get();
                        $prods_models = array_flatten(array_pluck($prods_models, 'product_id'));
                        // dd($prods_models);
                        $count = \DB::table('products')->select('id')->where('category_id', '=', $data['category'])
                            ->whereIn('id', $prods_models)->count();
                        return json_encode(['data' => $cabs, 'count' => $count]);

                    } else {

                        return 'error';

                    }

                case 'cab':
                    if(Cab::where('id', $data['cab'])->exists()) {
                        $models_year = Models::select('start_production', 'end_production')->where('cab_id', $data['cab'])
                            ->where('brand_id', $data['brand'])->groupBy('start_production')
                            ->groupBy('end_production')->get()->toArray();
                        $array_years = array_unique(array_flatten($models_year));
                        $null_index = array_search(null, $array_years);
                        if($null_index !== false)
                            $array_years[$null_index] = (int)date("Y");
                        $max = max($array_years);
                        $min = min($array_years);
                        $years = [];
                        for($i = $min; $i <= $max; $i++) {
                            $years[] = $i;
                        }
                        //товары из категории:
                        $prods_ids_by_category = \DB::table('products')->select('id')->where('category_id', '=',
                            $data['category'])->get();
                        $prods_ids_by_category = array_flatten(array_pluck($prods_ids_by_category, 'id'));
                        //модели по типу кузова (макра авто уже учтена):
                        $helper_models_ids = explode(',', $data['model']);
                        $model_by_cab_and_models = \DB::table('models')->select('id')->whereIn('id', $helper_models_ids)
                            ->where('cab_id', '=', $data['cab'])->get();
                        $model_by_cab_and_models = array_flatten(array_pluck($model_by_cab_and_models, 'id'));
                        //общие товары по двум верхним запросам
                        $prods_arr = \DB::table('products_model')->select('product_id')->whereIn('product_id', $prods_ids_by_category)
                            ->whereIn('model_id', $model_by_cab_and_models)->groupBy('product_id')->get();
                        return json_encode(['data' => $years, 'count' => count($prods_arr)]);
                    } else {
                        return 'error';
                    }

                case 'year':
                    if(Cab::where('id', $data['cab'])->exists()) {
                        $prods_ids_by_category = \DB::table('products')->select('id')->where('category_id', '=',
                            $data['category'])->get();
                        $prods_ids_by_category = array_flatten(array_pluck($prods_ids_by_category, 'id'));
                        $helper_models_ids = explode(',', $data['model']);
                        $model_by_cab_and_year = \DB::table('models')->select('id')->whereIn('id', $helper_models_ids)
                            ->where('cab_id', '=', $data['cab'])->where('start_production', '<=', (int)$data['year'])
                            ->where(function($query) use ($data){
                                 $query->where('end_production', '>=', (int)$data['year']);
                                 $query->orWhereNull('end_production');
                             })->get();
                        $model_by_cab_and_year = array_flatten(array_pluck($model_by_cab_and_year, 'id'));
                        $prods_arr = \DB::table('products_model')->select('product_id')->whereIn('product_id', $prods_ids_by_category)
                            ->whereIn('model_id', $model_by_cab_and_year)->groupBy('product_id')->get();
                        return json_encode(['count' => count($prods_arr)]);

                    } else {
                        return 'error';
                    }

            }

        } else {
            abort(404);
        }
    }


    public function headerSend(Request $request)
    {
        $data = $request->all();
        $post = \Purifier::clean($data);

        $rules = [
            'FIO' => 'required',
            'phone' => 'required',
        ];

        $messages = [
            'required' => 'Поле ":attribute" нельзя оставлять пустым!',
        ];

        $fieldsNames = [
            'FIO' => 'ФИО',
            'phone' => 'Телефон',
        ];

        $validator = Validator::make($post, $rules, $messages);
        $validator->setAttributeNames($fieldsNames);

        if ($validator->fails()) {
            return Redirect::back()
                ->withErrors($validator->errors())
                ->withInput();
        } else {

            \Mail::send(
                ['front.contact-call','front.contact-call-plain'],
                array(
                    'name' => $post['FIO'],
                    'phone' => $post['phone'],
                ), function ($message) {
                $message->from('info@la-ua.com');
                $message->to('andrii.pryimak@live.com', 'Admin')->subject('Новый заказ звонка');
            });

            return redirect()->back()->with(Session::flash('message', 'Ожидайте звонок оператора!'));
        }
    }


}
