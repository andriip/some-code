<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic as Image;

use App\Manufacturers;
use App\Characteristic;
use App\Brands;
use App\Models;
use App\Providers;
use App\Product;
use App\Category2;
use App\Products_characteristic;
use App\Product_provider;
use App\Product_model;
use App\Product_salon;
use App\Product_image;

use SoapBox\Formatter\Formatter;

class ProductController extends Controller
{

    public function validationRules($id=0)
    {
        return [
            'name' => 'required|max:250',
//            'slug' => 'required|max:250|unique:products,slug,'.$id.',id',
            'sku' => 'required|max:32|unique:products,sku,'.$id.',id',
            'price' => 'required|numeric',
            'count' => 'integer',
            'pop_koef'=>'integer',
            'video' => 'max:60|embed',
            'category_id' => 'required|exists:category2s,id',
            'manufacturers' => 'required|exists:manufacturers,id',
            'brand_id' => 'exists:brands,id',
            'model_id' => 'exists:models,id',
            'characteristics_id.*' => 'exists:characteristics,id',
            'characteristics_id' => 'required',
            'provider_original_price.*' => 'numeric',
            'provider_id.*' => 'required|exists:providers,id',
            'images.*' => 'max:3080|mimes:jpeg,png',
            'image' => 'max:3080|mimes:jpeg,png',
            'measure' => 'max:30',
            'comment' => 'max:250',
            'meta_title' => 'max:250',
            'meta_keywords' => 'max:250',
            'meta_description' => 'max:250',
            'meta_tags' => 'max:250|required',
            'description' => 'required|max:5000',
            'model_id.*' => 'exists:models,id',
            'provider_original_price' => 'required',
        ];
    }

    public function getFieldsNames()
    {
        return [
            'name' => 'Название товара',
            'price' => 'Цена',
            'count' => 'Количество',
            'pop_koef' => 'Коэффициент популярности',
            'images' => 'Изображения',
            'image' => 'Лицевое изображение',
            'category_id' => 'Категория',
            'manufacturers' => 'Производитель',
            'brand_id' => 'Марка',
            'model_id' => 'Модель',
            'characteristics_id' => 'Характеристики',
            'provider_id.*' => 'Поставщик',
            'measure' => 'Ед. измерения',
            'meta_description' => 'meta description',
            'meta_keywords' => 'meta keywords',
            'meta_tags' => 'meta tags',
            'description' => 'Описание',
            'slug' => 'Slug',
            'video' => 'Видео',
            'sku' => 'Артикул(SKU)',
            'meta_title' => 'Title',
        ];
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = '';
        if($request->has('search')) {
            $search = \Purifier::clean($request->get('search'));
            // Замена спецсимволов:
            $search_clean = str_replace(['%', "'", '"', '_', "\\"], ['\%', "\'", '\"', '\_', "\\\\"], $search);
            $products = Product::where('name', 'LIKE', '%'.$search_clean.'%')
                ->orWhere('sku', 'LIKE', '%'.$search_clean.'%')->paginate(20);
            $products->setPath(url('/').'/admin/products?search='.$search);
        } else {
            $products = Product::paginate(20);
        }
        $page = 'products.index';
        View::share(['page' => $page]);

        $cartTotal = \Cart::count() . ' товаров';

        return view('admin.products.product_index', ['products' => $products, 'search' => $search, 'cartTotal' => $cartTotal ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(\Gate::denies('admin')) {
			abort(404);
		}
        $manufacturers = Manufacturers::all();
        $characteristics = Characteristic::all();
        $providers = Providers::all();
        $brands = Brands::all();
        $page = 'products.create';
        View::share(['page' => $page]);
        return view('admin.products.product_create', ['manufacturers' => $manufacturers, 'characteristics' =>
            $characteristics, 'brands' => $brands, 'providers' => $providers]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(\Gate::denies('admin')) {
			abort(404);
		}

        if($request->isMethod('POST')) {
            // manufacturers - id производителя
            $data = $request->only('name', 'price', 'count', 'category_id', 'pop_koef', 'manufacturers', 'brand_id', 'model_id',
                'characteristic_id', 'characteristics', 'characteristics_id', 'provider_original_price', 'slug',
                'provider_currency', 'provider_code', 'provider_id', 'measure', 'comment', 'meta_description',
                'meta_keywords', 'model_id', 'sku', 'video', 'meta_tags', 'salon_id', 'meta_title');
            $data = \Purifier::clean($data);

            //dd($data);
            $data['description'] = \Purifier::clean($request->get('description'), 'description');
            $rules = ProductController::validationRules();
            $fieldsNames = ProductController::getFieldsNames();
    		$valMsg = [
    			'required' => 'Поле :attribute нельзя оставлять пустым',
    			'min' => 'Минимальная длина поля :attribute 3 символа',
    			'embed' => 'Поле Видео должно местить ссылку на видео из сервиса youtube',
    			'numeric' => 'Значение поля :attribute должно быть числом',
    			'unique' => 'Значение поля :attribute уже используется другим товаром',
    			'provider_original_price.required' => 'Добавьте как минимум одного поставщика',
    			'images.*.max' => 'Размер файла не должен превышать 3Мб',
    			'image.max' => 'Размер файла не должен превышать 3Мб',
    		];

            //dd($data);

    		$validator = Validator::make($data, $rules, $valMsg);
            $validator->setAttributeNames($fieldsNames);
            if($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            //dd($data);
            $catType = Category2::select('type')->where('id', $data['category_id'])->first()->toArray();
            if( $catType['type'] == '3' && empty( $data['model_id'] )){
                $validator->errors()->add('category_id', 'Продукты данной категории нельзя создавать без моделей!');
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }


            if($catType['type'] == "1"){
                $validator->errors()->add('parent_id', 'У родительской категории не может быть товаров!');
                //return $validator->errors();
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            $models = $data['model_id'];
            $salons = $data['salon_id'];
            $files = $request->file('images');
            $file = $request->file('image');
            $img = Image::make($file);
    		$imageName = '180-'.str_random(16).'.'.$file->guessExtension();
    		$img->resize(600, 600,
    			function ($constraint) {
    				$constraint->aspectRatio();
    			});
            $img->save(public_path().'/uploads/products/'.$imageName);

            $product = new Product();
            $product->name = $data['name'];

            $product->slug = Product::slugWorker($data['name']);

//            $product->slug = str_slug($data['slug']);

            $product->pop_koef = $data['pop_koef'];
            $product->price = $data['price'];
            $product->count = $data['count'];
            $product->sku = $data['sku'];
            $product->video = $data['video'];
            $product->image = $imageName;
            $product->description = $data['description'];
            $product->measure = $data['measure'];
            $product->manufacturer_id = $data['manufacturers'];
            $product->category_id = $data['category_id'];
            $product->comment = $data['comment'];
            $product->title = $data['meta_title'];
            $product->count = $data['count'];
            $product->meta_keywords = $data['meta_keywords'];
            $product->meta_description = $data['meta_description'];
            $product->meta_tags = $data['meta_tags'];
            $product->save();

            $prod_id = $product->id;

            foreach($files as $file) {
                $img = Image::make($file);
        		$fname = str_random(18).'.'.$file->guessExtension();
        		$img->resize(600, 600,
        			function ($constraint) {
        				$constraint->aspectRatio();
        			});
                $img->save(public_path().'/uploads/products/'.$fname);
                $prod_img = new Product_image();
                $prod_img->product_id = $prod_id;
                $prod_img->image = $fname;
                $prod_img->save();
            }

            foreach ($data['characteristics_id'] as $key => $value) {
                $characteristic = new Products_characteristic();
                $characteristic->product_id = $prod_id;
                $characteristic->characteristic_id = $value;
                $characteristic->value = $data['characteristics'][$key];
                $characteristic->save();
            }

            if($models != '') {
                foreach ($models as $key => $val) {
                    $product_model = new Product_model();
                    $product_model->product_id = $prod_id;
                    $product_model->model_id = $val;
                    $product_model->save();
                }
            }

            if($salons != '') {
                foreach ($salons as $key => $val) {
                    $product_salon = new Product_salon();
                    $product_salon->product_id = $prod_id;
                    $product_salon->salon_id = $val;
                    $product_salon->save();
                }
            }

            if($data['provider_id'] != "") {
                foreach ($data['provider_id'] as $key => $value) {
                    $providers = new Product_provider();
                    $providers->provider_id = $value;
                    $providers->product_id = $prod_id;
                    $providers->price = $data['provider_original_price'][$key];
                    $providers->currency = $data['provider_currency'][$key];
                    $providers->provider_code = $data['provider_code'][$key];
                    $providers->save();
                }
            }

            return redirect()->back()->with('success', 'Новый товар успешно создан!');
        } else {
            abort(404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(\Gate::denies('admin')) {
			abort(404);
		}

        if(Product::where('id', $id)->exists()) {
            $product = Product::where('id', $id)->first();
            //dd($product->product_characteristics);
            $manufacturers = Manufacturers::all();
            $characteristics = Characteristic::all();
            $providers = Providers::all();
            $brands = Brands::all();
            $page = 'products.index';
            View::share(['page' => $page]);
            return view('admin.products.product_edit', ['product' => $product, 'manufacturers' => $manufacturers,
                'characteristics' => $characteristics, 'providers' => $providers, 'brands' => $brands]);
        } else {
            abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(\Gate::denies('admin')) {
			abort(404);
		}


        if(Product::where('id', $id)->exists()) {
            $data = $request->only('name', 'price', 'count', 'category_id', 'pop_koef', 'manufacturers', 'brand_id', 'model_id',
                'characteristic_id', 'characteristics', 'characteristics_id', 'provider_original_price', 'slug',
                'provider_currency', 'provider_code', 'provider_id', 'measure', 'comment', 'meta_description',
                'meta_keywords', 'model_id', 'removed_images', 'sku', 'video', 'meta_tags','salon_id', 'meta_title');
            $data = \Purifier::clean($data);
            $data['description'] = \Purifier::clean($request->get('description'), 'description');
            $rules = ProductController::validationRules($id);
            $fieldsNames = ProductController::getFieldsNames();
            $valMsg = [
                'required' => 'Поле :attribute нельзя оставлять пустым',
                'min' => 'Минимальная длина поля :attribute 3 символа',
                'embed' => 'Поле Видео должно местить ссылку на видео из сервиса youtube',
                'numeric' => 'Значение поля :attribute должно быть числом',
                'unique' => 'Значение поля :attribute уже используется другим товаром',
                'images.*.max' => 'Размер файла не должен превышать 3Мб',
                'image.max' => 'Размер файла не должен превышать 3Мб',
                'characteristics_id.required' => 'Товар должен местить как минимум одну характеристику',
            ];


            $validator = Validator::make($data, $rules, $valMsg);
            $validator->setAttributeNames($fieldsNames);
            if($validator->fails()) {
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }


            $cat_type = Category2::select('type')->where('id', $data['category_id'])->first();
            if($cat_type->type == "1"){
                $validator->errors()->add('parent_id', 'У родительской категории не может быть товаров!');
                //return $validator->errors();
                return redirect()->back()->withErrors($validator->errors())->withInput();
            }

            $catType = Category2::select('type')->where('id', $data['category_id'])->first()->toArray();
                if( $catType['type'] == '3' && empty( $data['model_id'] )){
                    $validator->errors()->add('category_id', 'Продукты данной категории нельзя создавать без моделей!');
                    return redirect()->back()->withErrors($validator->errors())->withInput();
                }


            $product = Product::where('id', $id)->first();
            //изображения, которые удалили из галереи товара:
            if($data['removed_images'] != "") {
                $images_ids = explode(',', $data['removed_images']);
                foreach($product->images as $prod_image) {
                    if(in_array($prod_image->id, $images_ids)) {
                        if (\File::exists(public_path().'/uploads/products/'.$prod_image->image))
                            \File::delete(public_path().'/uploads/products/'.$prod_image->image);
                    }
                }
                \DB::table('product_images')->where('product_id', $id)->whereIn('id', $images_ids)->delete();
            }
            $imageName = null;
            if($request->hasFile('image')) {
                $file = $request->file('image');
                $img = Image::make($file);
        		$imageName = '180-'.str_random(16).'.'.$file->guessExtension();
        		$img->resize(600, 600,
        			function ($constraint) {
        				$constraint->aspectRatio();
        			});
                $img->save(public_path().'/uploads/products/'.$imageName);
                if (\File::exists(public_path().'/uploads/products/'.$product->image))
                    \File::delete(public_path().'/uploads/products/'.$product->image);
            }

            $product->name = $data['name'];

            $product->slug = Product::slugWorker($data['name'], $product->id);

//            $product->slug = str_slug($data['slug']);

            $product->pop_koef = $data['pop_koef'];
            $product->price = $data['price'];
            $product->count = $data['count'];
            $product->video = $data['video'];
            $product->sku = $data['sku'];
            $product->image = $imageName != null ? $imageName : $product->image;
            $product->description = $data['description'];
            $product->measure = $data['measure'];
            $product->manufacturer_id = $data['manufacturers'];
            $product->category_id = $data['category_id'];
            $product->comment = $data['comment'];
            $product->title = $data['meta_title'];
            $product->count = $data['count'];
            $product->meta_keywords = $data['meta_keywords'];
            $product->meta_description = $data['meta_description'];
            $product->meta_tags = $data['meta_tags'];
            $product->update();

            $prod_id = $product->id;
            //новые изображения в галереи товара
            if($request->hasFile('images')) {
                $files = $request->file('images');
                foreach($files as $file) {
                    $img = Image::make($file);
            		$fname = str_random(18).'.'.$file->guessExtension();
            		$img->resize(600, 600,
            			function ($constraint) {
            				$constraint->aspectRatio();
            			});
                    $img->save(public_path().'/uploads/products/'.$fname);
                    $prod_img = new Product_image();
                    $prod_img->product_id = $prod_id;
                    $prod_img->image = $fname;
                    $prod_img->save();
                }
            }

            \DB::table('product_characteristics')->where('product_id', $prod_id)->delete();
            foreach ($data['characteristics_id'] as $key => $value) {
                $characteristic = new Products_characteristic();
                $characteristic->product_id = $prod_id;
                $characteristic->characteristic_id = $value;
                $characteristic->value = $data['characteristics'][$key];
                $characteristic->save();
            }

            $models = $data['model_id'];
            \DB::table('products_model')->where('product_id', $prod_id)->delete();
            if($models != '') {
                foreach ($models as $key => $val) {
                    $product_model = new Product_model();
                    $product_model->product_id = $prod_id;
                    $product_model->model_id = $val;
                    $product_model->save();
                }
            }

            $salons = $data['salon_id'];
            \DB::table('product_salons')->where('product_id', $prod_id)->delete();
            if($salons != '') {
                foreach ($salons as $key => $val) {
                    $product_salon = new Product_salon();
                    $product_salon->product_id = $prod_id;
                    $product_salon->salon_id = $val;
                    $product_salon->save();
                }
            }

            \DB::table('product_providers')->where('product_id', $prod_id)->delete();
            if($data['provider_id'] != "")
                foreach ($data['provider_id'] as $key => $value) {
                    $providers = new Product_provider();
                    $providers->provider_id = $value;
                    $providers->product_id = $prod_id;
                    $providers->price = $data['provider_original_price'][$key];
                    $providers->currency = $data['provider_currency'][$key];
                    $providers->provider_code = $data['provider_code'][$key];
                    $providers->save();
                }

            return redirect()->back()->with('success', 'Изменения сохранены!');
        } else {
            abort(404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(\Gate::denies('admin')) {
			abort(404);
		}
        if(Product::where('id', $id)->exists()) {
            $product = Product::where('id', $id)->first();
            if (\File::exists(public_path().'/uploads/products/'.$product->image))
                \File::delete(public_path().'/uploads/products/'.$product->image);
            $images = $product->images;
            if($images->count() > 0) {
                foreach ($images as $image) {
                    if (\File::exists(public_path().'/uploads/products/'.$image->image))
        				\File::delete(public_path().'/uploads/products/'.$image->image);
                }
            }
            $product->images()->delete();
            \DB::table('product_characteristics')->where('product_id', '=', $id)->delete();
            \DB::table('products_model')->where('product_id', '=', $id)->delete();
            \DB::table('product_salons')->where('product_id', '=', $id)->delete();
            \DB::table('product_providers')->where('product_id', '=', $id)->delete();
            \DB::table('product_orders')->where('product_id', '=', $id)->update(['product_id' => 0]);
            $product->delete();
            return redirect()->back()->with('success', 'Товар успешно удален');
        } else {
            return redirect()->back()->with('error', 'Ошибка, такого товара не существует');
        }
    }

    public function getModelsByBrand(Request $request)
    {
        if($request->ajax()) {
            $escape_str = $request->get('escape');
            $escape = [0];
            if(strlen($escape_str) > 0) {
                $escape = explode(',', $escape_str);
            }
            if(Brands::where('id', $request->get('brand_id'))->exists()) {
                $brand = Brands::where('id', $request->get('brand_id'))->first();
                $models = $brand->models()->whereNotIn('id', $escape)->get()->toArray();
                return json_encode(['data' => $models]);
            } else {
                return 'error';
            }
        }
        abort(404);
    }

    public function searchProduct(Request $request)
    {
        $search = \Purifier::clean($request->get('search'));
        $result = Product::where('name', 'LIKE', '%'.$search.'%')->orWhere('sku', 'LIKE', '%'.$search.'%')->paginate(1);
        $page = 'products.index';
        View::share(['page' => $page]);
        return view('admin.products.product_index', ['products' => $result]);
    }

    public function test()
    {
        try {
            $contents = \File::get('uploads/test.xml');
        } catch (Illuminate\Filesystem\FileNotFoundException $exception) {
            die("The file doesn't exist");
        }
        $formatter = Formatter::make($contents, Formatter::XML);
        dd($formatter->toArray());
    }

}
