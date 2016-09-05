<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    public function manufacturer()
    {
        return $this->hasOne('App\Manufacturers', 'id', 'manufacturer_id');
    }

    public function category()
    {
        return $this->hasOne('App\Category2', 'id', 'category_id');
    }

    public function images()
    {
        return $this->hasMany('App\Product_image', 'product_id', 'id');
    }

    public function models()
    {
        return $this->belongsToMany('App\Models', 'products_model', 'product_id', 'model_id');
    }

    public function salons()
    {
        return $this->belongsToMany('App\Salon', 'product_salons', 'product_id', 'salon_id');
    }

    public function product_providers()
    {
        return $this->hasMany('App\Product_provider', 'product_id', 'id');
    }

    public function product_characteristics()
    {
        return $this->hasMany('App\Products_characteristic', 'product_id', 'id');
    }

    //связь между продуктами в заказе и продуктами
    public function order_product()
    {
        return $this->belongsTo('App\OrderProduct', 'id', 'product_id');
    }

    public function create_link(){

        $p = $this;

        //$model = Product_model::select('model_id')->where('product_id', $p->id)->get()/*->toArray()*/;

        // pr slug
        $productSlug = $this->slug;

        //model string
        $model = $this->models->toArray();

        $modSts = '';
        $modelArr = [];
            foreach($model as $val){
                $modSts .=  $val['slug'] . ';';
                $modelArr[] = $val['brand_id'];
            }
        $modSts = substr($modSts, 0, -1);

        //brand slug
        $brand = Brands::select('brand_name')->whereIn('id', $modelArr)->first();

        //category slug
        $category = Category2::select('id','slug', 'parent_id', 'type')->where('id', $this->category_id)->first();


        if( $category['type'] == 2 ) { //product category


            if( Category2::select('parent_id', 'type')->where('id', $category['parent_id'])->exists() ){

                //parentCategory slug
                $parentCategory = Category2::select('id', 'slug' , 'parent_id', 'type')->where('id', $category['parent_id'])->first();
                $string = $parentCategory['slug'] . '/' . $category['slug'] . '/'. $productSlug;
                return $string;

            } else {

                $string = $category['slug'] . '/'. $productSlug;
                return $string;

            }


        } else if($category['type'] == 3 ){

            if( Category2::select('parent_id', 'type')->where('id', $category['parent_id'])->exists() ){

                //parentCategory slug
                $parentCategory = Category2::select('id', 'slug' , 'parent_id', 'type')->where('id', $category['parent_id'])->first();
                $string = $parentCategory['slug'] . '/' . $category['slug'] . '/' . $brand['brand_name'] . '/' . $modSts . '/'. $productSlug;
                return $string;

            } else {
                $string =  $category['slug'] . '/' . $brand['brand_name'] . '/' . $modSts . '/'. $productSlug;
                return $string;
            }


        } else if($category['type'] == 1){

            //parentCategory slug
            $parentCategory = Category2::select('id', 'slug' , 'parent_id', 'type')->where('id', $category['parent_id'])->first();
            $string = $parentCategory['slug'] . '/' . $category['slug'] . '/' . $brand['brand_name'] . '/' . $modSts . '/'. $productSlug;
            return $string;

        }

    }



    public static function product_link($id){

        $p = Product::find($id);

        // pr slug
        $productSlug = $p->slug;

        //model string
        $model = $p->models->toArray();

        $modSts = '';
        $modelArr = [];
        foreach($model as $val){
            $modSts .=  $val['slug'] . ';';
            $modelArr[] = $val['brand_id'];
        }
        $modSts = substr($modSts, 0, -1);

        //brand slug
        $brand = Brands::select('brand_name')->whereIn('id', $modelArr)->first();

        //category slug
        $category = Category2::select('id','slug', 'parent_id', 'type')->where('id', $p->category_id)->first();


        if( $category['type'] == 2 ) { //product category


            if( Category2::select('parent_id', 'type')->where('id', $category['parent_id'])->exists() ){

                //parentCategory slug
                $parentCategory = Category2::select('id', 'slug' , 'parent_id', 'type')->where('id', $category['parent_id'])->first();
                $string = $parentCategory['slug'] . '/' . $category['slug'] . '/'. $productSlug;
                return $string;

            } else {

                $string = $category['slug'] . '/'. $productSlug;
                return $string;

            }


        } else if($category['type'] == 3 ){

            if( Category2::select('parent_id', 'type')->where('id', $category['parent_id'])->exists() ){

                //parentCategory slug
                $parentCategory = Category2::select('id', 'slug' , 'parent_id', 'type')->where('id', $category['parent_id'])->first();
                $string = $parentCategory['slug'] . '/' . $category['slug'] . '/' . $brand['brand_name'] . '/' . $modSts . '/'. $productSlug;
                return $string;

            } else {
                $string =  $category['slug'] . '/' . $brand['brand_name'] . '/' . $modSts . '/'. $productSlug;
                return $string;
            }


        } else if($category['type'] == 1){

            //parentCategory slug
            $parentCategory = Category2::select('id', 'slug' , 'parent_id', 'type')->where('id', $category['parent_id'])->first();
            $string = $parentCategory['slug'] . '/' . $category['slug'] . '/' . $brand['brand_name'] . '/' . $modSts . '/'. $productSlug;
            return $string;

        }

    }




    //формирования слага автоматически
    public static function slugWorker($str, $id=0)
    {
        $slug = Product::toSlug($str);
        if(Product::where('slug', 'LIKE', $slug.'%')->exists())
        {
            $item = Product::where('slug', 'LIKE', $slug.'%')->orderBy('id', 'DESC')
                ->first();
            $old = $item->slug;
            if($item->id == $id)
                return $slug;
            $subnum = substr($old, strrpos($old, '-') + 1);
            if(is_numeric($subnum) === true)
            {
                $newId = (int) $subnum + 1;
                $newSlug = strrev(implode(strrev($newId), explode($subnum, strrev($old), 2)));
                return $newSlug;
            }
            else
            {
                return $slug.'-1';
            }
        }
        else
        {
            return $slug;
        }
    }

    public static function toSlug($str)
    {
        if(preg_match('/[А-Яа-яЁёїЇіІєЄ]/u', $str))
        {
            $cyr  = ['а','б','в','г','д','e','ж','з','и','й','к','л','м','н','о','п','р','с','т','у', 'ф','х','ц','ч','ш',
                'щ','ъ','ь', 'ю','я','А','Б','В','Г','Д','Е','Ж','З','И','Й','К','Л','М','Н','О','П','Р', 'С','Т','У', 'Ф',
                'Х','Ц','Ч','Ш','Щ','Ъ','Ь', 'Ю','Я', 'і', 'І', 'ї', 'Ї', 'є', 'Є'];
            $lat = ['a','b','v','g','d','e','zh','z','i','y','k','l','m','n','o','p','r','s','t','u', 'f' ,'h' ,'ts' ,'ch',
                'sh' ,'sht' ,'a' ,'y' ,'yu' ,'ya','A','B','V','G','D','E','Zh', 'Z','I','Y','K','L', 'M','N','O','P','R',
                'S','T','U', 'F' ,'H' ,'Ts' ,'Ch','Sh' ,'Sht' ,'A' ,'Y' ,'Yu' ,'Ya', 'i', 'I', 'ji', 'JI', 'e', 'E'];
            $result = str_replace($cyr, $lat, $str);
            return str_slug($result, '-');
        }
        else
            return str_slug($str, '-');
    }
    // public function characteristics()
    // {
    //     return $this->morphMany('App\Products_characteristic', 'characteristic');
    // }
}
