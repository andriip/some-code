<?php namespace App\Http\Controllers;

use App\Category2 as Category;
use App\Product;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use PhpSpec\Process\Context\JsonExecutionContext;


class CategoryController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{

		$search = '';
		if($request->has('search')) {
			$search = \Purifier::clean($request->get('search'));
			// Замена спецсимволов:
			$search_clean = str_replace(['%', "'", '"', '_', "\\"], ['\%', "\'", '\"', '\_', "\\\\"], $search);
			$categories = Category::where('name', 'LIKE', '%'.$search_clean.'%')->paginate(20);
			$categories->setPath(url('/').'/admin/categories?search='.$search);
		} else {
			$categories = Category::paginate(20);
		}
		$page = 'categories.index';
		View::share(['page' => $page]);
		return view('admin.categories.categoryindex', ['categories' => $categories, 'search' => $search]);

/*		$categories = Category::paginate(20);
		$page = 'products.categories.index';
		View::share(['page' => $page]);
		return View::make('admin.categories.categoryindex')
			->with('categories', $categories);*/

	}


	public function findCategories(Request $request)
	{
		$data = $request->only('q', 'ex');
		if($request->ajax())
		{
			if(!empty($_POST['inutText'])){
				$text = $_POST['inutText'];

				$results = Category::getCategoriesFromName($text);
					if($results){
						$html = '';
						foreach ($results as $result){
							$str = '';
							foreach($result->getAncestorsAndSelf() as $nn) {
								$str = $str . ($str == '' ? '' : ' -> ') . $nn->name;
							}
							$html.= '<li class="categoryListDrop" onClick="insertValueInput($(this))" categoryName="'
							.$str.'" categoryid="'.$result->id.'" >'.$str.'</li>';
						}
						return $html;
						exit;

					} else {
						$html = 'noItemFind';
						//print_r(json_encode($html));
						return $html;
						exit;
					}

			} elseif(strlen($data['q']) > 0) {
				$parent = null;
				if(!isset($data['ex']))
					$resp = Category::where('name', 'LIKE', '%'.$data['q'].'%')->get();
				else {
					$resp = Category::where('name', 'LIKE', '%'.$data['q'].'%')->where('id', '!=', $data['ex'])->get();
					$parent = Category::where('id', $data['ex'])->first();
				}
				$arr = [];
				$i = 0;
				//KRASIVO
				foreach($resp as $pizda) {
					$str = '';
					if(isset($parent)) {
						if(!$pizda->isDescendantOf($parent)) {
							foreach($pizda->getAncestorsAndSelf() as $nn) {
								$str = $str . ($str == '' ? '' : ' -> ') . $nn->name;
							}
							$arr[$i]['name'] = $str;
							$arr[$i]['id'] = $pizda->id;
							$i++;
						}
					} else {
						foreach($pizda->getAncestorsAndSelf() as $nn) {
							$str = $str . ($str == '' ? '' : ' -> ') . $nn->name;
						}
						$arr[$i]['name'] = $str;
						$arr[$i]['id'] = $pizda->id;
						$i++;
					}
				}
				return json_encode([
					'total_count' => $resp->count(),
					'incomplete_results' => false,
					'items' => $arr
				]);
			}
		}
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if(\Gate::denies('admin')) {
			abort(404);
		}
		$page = 'products.categories.index';
		View::share(['page' => $page]);
		return view('admin.categories.categorycreate');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		if(\Gate::denies('admin')) {
			abort(404);
		}
		$data = $request->only('name','parent_id', 'slug', 'meta_title', 'meta_description', 'meta_keywords','catImg', 'description',
			'type');
		$successOrErrors = Category::createCategory($data);
		if($successOrErrors === true) {
			Session::flash('message', 'Удачно добавленая категория!');
			return Redirect::to('/admin/categories');
		} else {
			return redirect()->back()->withErrors($successOrErrors)->withInput();
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if(\Gate::denies('admin')) {
			abort(404);
		}
		$category = Category::find($id);
		$page = 'products.categories.index';
		View::share(['page' => $page]);
		return View::make('admin.categories.categoryedit')
			->with('category', $category);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		if(\Gate::denies('admin')) {
			abort(404);
		}
		$data = Input::only('name', 'slug', 'meta_description', 'meta_title', 'meta_keywords', 'parent_id', 'catImg', 'description',
			'type', 'issetParentCategory');
		$successOrErrors = Category::updateCategory($data, $id);
		if($successOrErrors === true)
			return redirect()->to('/admin/categories')->with('success', 'Изменения сохранены');
		elseif($successOrErrors === false)
			return redirect()->to('/admin/categories')->with('error', 'Обновите страницу и попробуйте еще раз');
		else
			return redirect()->back()->withErrors($successOrErrors)->withInput();
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		if(\Gate::denies('admin')) {
			abort(404);
		}
		$successOrErrors = Category::deleteCategory($id);
		if($successOrErrors){
			//return redirect()->back();
			return redirect()->back()->with('deleteMsg', 'Категория успешно удалена!');
		} else {
			//return redirect()->back();
			return redirect()->back()->with('deleteErr', 'Ошибка удаления!');
		}
	}

}
