@extends('admin.main')

@section('page-title')
Панель управления
@endsection

@section('main-content')

  <div class="wrap-content container" id="container">
      <!-- start: DASHBOARD TITLE -->
      <section id="page-title" class="padding-top-15 padding-bottom-15">
          <div class="row">
              <div class="col-sm-7">
                  <div class="margin-top-0 margin-bottom-0"><h1 class="mainTitle">Панель управления</h1></div>
                  {{--<span class="mainDescription">overview &amp; stats </span>--}}
              </div>
          </div>
      </section>
      <!-- end: DASHBOARD TITLE -->
      <!-- start: FEATURED BOX LINKS -->
      <div class="container-fluid container-fullw bg-white">
          <div class="row">
              <div class="col-sm-4">
                  <div class="panel panel-white no-radius text-center">
                      <div class="panel-body">
                          <span class="fa-stack fa-2x"> <i class="fa fa-shopping-cart"></i> <i class="shopping-cart"></i> </span>
                          <h2 class="StepTitle">Управления товарами</h2>
                          <p class="links cl-effect-1">
                              <a href="{!! URL::to('admin/products') !!}">
                                  Список товаров
                              </a>
                              <br>
                              <br>
                              <a href="{!! URL::to('admin/products/create') !!}">
                                  Создание товара
                              </a>
                              <br>
                              <br>
                              <a href="{!! URL::to('admin/categories') !!}">
                                  Список категорий
                              </a>
                              <br>
                              <br>
                              <a href="{!! URL::to('admin/price_policy') !!}">
                                  Ценовая политика товаров
                              </a>
                          </p>
                      </div>
                  </div>
              </div>


              <div class="col-sm-4">
                  <div class="panel panel-white no-radius text-center">
                      <div class="panel-body">
                          <span class="fa-stack fa-2x"> <i class="fa fa-bar-chart-o"></i> </span>
                          <h2 class="StepTitle">Характеристики товаров</h2>
                          <p class="links cl-effect-1">
                              <a href="{!! URL::to('admin/characteristics') !!}">
                                  Список характеристик
                              </a>
                              <br>
                              <br>
                              <a href="{!! URL::to('admin/characteristics/create') !!}">
                                  Создание характеристики
                              </a>
                          </p>
                      </div>
                  </div>
              </div>

              <div class="col-sm-4">
                  <div class="panel panel-white no-radius text-center">
                      <div class="panel-body">
                          <span class="fa-stack fa-2x"> <i class="fa fa-car"></i> <i class="car"></i> </span>
                          <h2 class="StepTitle">Управления моделями авто</h2>
                          <p class="cl-effect-1">
                              <a href="{!! URL::to('admin/brands') !!}">
                                  Список марок авто
                              </a>
                              <br>
                              <br>
                              <a href="{!! URL::to('admin/cabs') !!}">
                                  Список кузовов
                              </a>
                              <br>
                              <br>
                              <a href="{!! URL::to('admin/models') !!}">
                                  Список моделей авто
                              </a>
                              <br>
                              <br>
                              <a href="/admin/salons">
                                  Список салонов авто
                              </a>
                              <br>
                              <br>
                              <!--a href="/admin/keywords_list">
                                  Список ключевиков
                              </a-->
                          </p>
                      </div>
                  </div>
              </div>

              <div class="col-sm-4">
                  <div class="panel panel-white no-radius text-center">
                      <div class="panel-body">
                          <span class="fa-stack fa-2x"> <i class="fa fa-child"></i> <i class="child"></i> </span>
                          <h2 class="StepTitle">Управление пользователями</h2>
                          <p class="links cl-effect-1">
                              <a href="{!! URL::to('admin/users') !!}">
                                  Список пользователей
                              </a>
                              <br>
                              <br>
                              <a href="{!! URL::to('admin/users/create') !!}">
                                  Создание пользователя
                              </a>
                          </p>
                      </div>
                  </div>
              </div>

              <div class="col-sm-4">
                  <div class="panel panel-white no-radius text-center">
                      <div class="panel-body">
                          <span class="fa-stack fa-2x"> <i class="fa fa-truck"></i> </span>
                          <h3 class="StepTitle">Управление<br> поставщиками<br>и производителями</h3>
                          <p class="links cl-effect-1">
                              <a href="{!! URL::to('/admin/providers') !!}">
                                  Список поставщиков
                              </a>
                              <br>
                              <br>
                              <a href="{!! URL::to('/admin/manufacturers') !!}">
                                  Список производителей
                              </a>
                          </p>
                      </div>
                  </div>
              </div>


              <div class="col-sm-4">
                  <div class="panel panel-white no-radius text-center">
                      <div class="panel-body">
                          <span class="fa-stack fa-2x"> <i class="fa fa-cc-visa"></i> </span>
                          <h2 class="StepTitle">Управление заказами</h2>
                          <p class="links cl-effect-1">
                              <a href="{!! URL::to('admin/orders') !!}">
                                  Список заказов
                              </a>
                              <br>
                              <br>
                              <a href="{!! URL::to('admin/orders/create') !!}">
                                  Создание заказа
                              </a>
                              <br>
                              <br>
                              <a href="{!! URL::to('admin/status') !!}">
                                  Список статусов заказа
                              </a>
                              <br>
                              <br>
                              <a href="{!! URL::to('admin/ship_method') !!}">
                                  Список возможных <br> способов доставки
                              </a>
                          </p>
                      </div>
                  </div>
              </div>


              <div class="col-sm-4">
                  <div class="panel panel-white no-radius text-center">
                      <div class="panel-body">
                          <span class="fa-stack fa-2x"> <i class="fa fa-gear"></i> <i class="gear"></i> </span>
                          <h2 class="StepTitle">Управление настройками <br> главной страницы</h2>
                          <p class="links cl-effect-1">
                              <a href="{!! URL::to('admin/mainPageSettings') !!}">
                                  Главная страница
                              </a>
                              <br>
                              <br>
                              <a href="{!! URL::to('admin/articles') !!}">
                                  Полезные статьи
                              </a>
                          </p>
                      </div>
                  </div>
              </div>


              <div class="col-sm-4">
                  <div class="panel panel-white no-radius text-center">
                      <div class="panel-body">
                          <span class="fa-stack fa-2x"> <i class="fa fa-file-text-o"></i> <i class="file"></i> </span>
                          <h2 class="StepTitle">Управление отзывами</h2>
                          <p class="links cl-effect-1">
                              <a href="{!! URL::to('admin/mainRecall') !!}">
                                  Все отзывы
                              </a>
                          </p>
                      </div>
                  </div>
              </div>

          </div>
      </div>

      <!-- end: FOURTH SECTION -->
  </div>

@endsection
