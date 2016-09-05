@extends('admin.main')

@section('page-title')
    Создать новый товар
@endsection

@section('includable')
    <link href="/vendor/select2/select2.min.css" rel="stylesheet" media="screen">
    <link href="/vendor/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet" media="screen">
@endsection

@section('main-content')

            <div class="wrap-content container" id="container">
                <!-- start: PAGE TITLE -->
                <section id="page-title">
                    <div class="row">
                        <div class="margin-top-0 margin-bottom-0">
                            <h1 class="mainTitle" align="center">Создать новый товар</h1>
                        </div>
                    </div>

                    <div class="row">
                        <hr>
                        <div class="margin-top-0 margin-bottom-0">
                            <nav id="cl-effect-4" class="links cl-effect-4">
                                <a href="{!! URL::to('admin/products') !!}">
                                    Назад к списку товаров
                                </a>
                            </nav>
                        </div>
                    </div>
                </section>

                <!-- onclick="eatFood()start: DYNAMIC TABLE -->


                <div class="row">

                    @if(session('success'))
                        <div class="col-md-6 col-md-offset-3">
                            <div class="alert alert-success margin-top-25" role="alert">
                                <button class="close" type="button" data-dismiss="alert" aria-label="Закрыть">
                                    <span aria-hidden="true">×</span>
                                </button>
                                <strong>Выполнено!</strong>
                                {{session('success')}}
                            </div>
                        </div>
                    @endif

                    @if (count($errors) > 0)
                        <div class="col-md-6 col-md-offset-3">
                            @foreach ($errors->all() as $error)
                                <div class="alert alert-danger margin-top-25">
                                    <button class="close" type="button" data-dismiss="alert" aria-label="Закрыть">
                                        <span aria-hidden="true">×</span>
                                    </button>
                                    {{ $error }}
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="col-md-8 col-md-offset-2 margin-top-25">

                        <form method="POST" action="{{ url('/').'/admin/products'}}" enctype="multipart/form-data">

                            {!! csrf_field() !!}

                            <div class="form-group">
                                <label class="control-label">
                                    <label for="name">Название товара</label>
                                    <span class="symbol required"></span>
                                </label>
                                <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                                    id="name" maxlength="200" required>
                            </div>

                            <div class="form-group">
                                <label class="control-label">
                                    <label for="name">Артикул(SKU)</label>
                                    <span class="symbol required"></span>
                                </label>
                                <input required type="text" class="form-control" name="sku" value="{{ old('sku') }}" id="sku" title=""
                                    maxlength="30" pattern="[a-zA-Z\d]*[-]?[a-zA-Z\d]*" data-original-title=""
                                    data-placement="top" data-trigger="hover" data-title="Идентификатор товарной позиции"
                                    data-content="Поле может содержать латинские буквы, цифры и знак -" data-toggle="popover">
                            </div>

                            {{--<div class="form-group">--}}
                                {{--<label class="control-label">--}}
                                    {{--<label for="name">Slug</label>--}}
                                    {{--<span class="symbol required"></span>--}}
                                {{--</label>--}}
                                {{--<input type="text" class="form-control" name="slug" value="{{ old('slug') }}"--}}
                                    {{--id="slug" maxlength="250" required pattern="[a-zA-Z/-]+" data-original-title="" title="" data-toggle="popover"--}}
                                    {{--data-placement="top" data-trigger="hover" data-title="Сегмент в ссылке"--}}
                                    {{--data-content="Например, если товар Капот Audi A4 (B6,8E), то Slug будет выглядеть примерно так: kapot-audi-a4-b6-8e">--}}
                            {{--</div>--}}

                            <div class="form-group">
                                <label class="control-label">
                                    <label for="name">Коэффициент популярности</label>
                                    <span class="symbol required"></span>
                                </label>
                                <input required type="text" class="form-control" name="pop_koef" value="{{ old('pop_koef') }}"
                                       id="pop_koef" maxlength="250"  data-original-title="" title="" data-toggle="popover"
                                       data-placement="top" data-trigger="hover" data-title="Коэффициент популярности товара"
                                       data-content="Введите число, от 1 до 100. Популярным товар становится с наибольшим значением этого коэффициента">
                            </div>

                            <div class="form-group bootstrap-touchspin">
                                <label class="control-label">
                                    <label for="name">Цена (грн.)</label>
                                    <span class="symbol required"></span>
                                </label>
                                <span style="display: none;" class="input-group-addon bootstrap-touchspin-prefix"></span>
                                <input style="display: block;" class="form-control" id="price" name="price" touchspin=""
                                    value="{{ old('price') == null ? '1' : old('price') }}" data-verticalbuttons="true"
                                    data-verticalupclass="ti-angle-up" data-verticaldownclass="ti-angle-down" type="text">
                                <span style="display: none;" class="input-group-addon bootstrap-touchspin-postfix"></span>
                            </div>

                            <div class="form-group bootstrap-touchspin">
                                <label class="control-label">
                                    <label for="name">Количество</label>
                                </label>
                                <span style="display: none;" class="input-group-addon bootstrap-touchspin-prefix"></span>
                                <input style="display: block;" value="{{ old('count') == null ? '0' : old('count') }}"
                                    name="count" touchspin="" data-verticalbuttons="true" data-verticalupclass="ti-angle-up"
                                    data-verticaldownclass="ti-angle-down" type="text" class="form-control" id="count">
                                <span style="display: none;" class="input-group-addon bootstrap-touchspin-postfix"></span>
                            </div>

                            <div class="form-group">
                                <label class="control-label">
                                    <label for="meta_title">Title</label>
                                </label>
                                <input type="text" class="form-control" name="meta_title" value="{{ old('meta_title') }}"
                                       id="meta_title" maxlength="250">
                            </div>

                            <div class="form-group">
                                <label class="control-label">
                                    <label for="meta_keywords">Meta keywords</label>
                                </label>
                                <input type="text" class="form-control" name="meta_keywords" value="{{ old('meta_keywords') }}"
                                    id="meta_keywords" maxlength="250">
                            </div>

                            <div class="form-group">
                                <label class="control-label">
                                    <label for="meta_description">Meta description</label>
                                </label>
                                <input type="text" class="form-control" name="meta_description" value="{{ old('meta_description') }}"
                                    id="meta_description" maxlength="250">
                            </div>

                            <div class="form-group">
                                <label class="control-label">
                                    <label for="meta_tags">Tags</label>
                                    <span class="symbol required"></span>
                                </label>
                                <input required type="text" class="form-control" name="meta_tags" value="{{ old('meta_tags') }}"
                                       id="meta_tags" maxlength="250" data-original-title="" title="" data-toggle="popover"
                                       data-placement="top" data-trigger="hover" data-title="Теги товара"
                                       data-content="Поле может содержать как латинские символы, так и символы криллицей, идущие через ','. Например: kapot, капот, капотик">
                            </div>

                            <div class="form-group">
                                <label>Изображения <span class="symbol required"></span></label>
                                <input required type="file" name="images[]" multiple accept="image/jpeg,image/x-png">
                            </div>

                            <div class="form-group">
                                <label>Лицевое изображение <span class="symbol required"></span></label>
                                <input required type="file" name="image" accept="image/jpeg,image/x-png">
                            </div>

                            <div class="form-group">
                                <label>Видео</label>
                                <input type="text" name="video" class="form-control" maxlength="60"
                                    value="{{ old('video') }}" data-original-title="" title="" data-toggle="popover"
                                    data-placement="top" data-trigger="hover" data-title="Видео"
                                    data-content="Поле должно местить ссылку на видео из сервиса youtube. Пример ссылки: https://youtu.be/OuA5BP-tESt">
                            </div>

                            <div class="form-group">
                                <label>Ед. измерения</label>
                                <input type="text" name="measure" class="form-control" maxlength="30">
                            </div>

                            <div class="form-group">
                                <label for="select2">Категория <span class="symbol required"></span></label>
                                <select id="category_select" name="category_id" class="js-example-placeholder-single js-states form-control">
                                    <option></option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Производитель <span class="symbol required"></span></label>
                                <select class="form-control" name="manufacturers" id="manufacturers">
                                    <option></option>
                                    @foreach($manufacturers as $mnf)
                                        <option value="{{$mnf->id}}">{{$mnf->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Марка </label>
                                <select class="form-control" name="brand_id" id="brand_id">
                                    <option></option>
                                    @foreach($brands as $brand)
                                        <option value="{{$brand->id}}">{{$brand->brand_name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group" id="models_select" style="display: none;">
                                <label>Модель </label>
                                <select class="form-control" name="model_id" id="model_id">
                                    <option></option>
                                </select>
                            </div>

                            <div class="form-group">
                                <table class="table table-hover" id="auto_models_table" style="display: none;">
                                    <thead>
                                        <tr>
                                            <th>Модель</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="models_table">

                                    </tbody>
                                </table>
                            </div>

                            <div class="form-group">
                                <label>Характеристики <span class="symbol required"></span></label>
                                <select class="form-control" name="characteristic_id" id="characteristic_id">
                                    <option></option>
                                    @foreach($characteristics as $char)
                                        <option value="{{$char->id}}">{{$char->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group" id="append_char">
                            </div>

                            <div class="form-group">
                                <label>Поставщик </label>
                                <span class="symbol required"></span>
                                <select class="form-control" name="providers" id="providers">
                                    <option></option>
                                    @foreach($providers as $provider)
                                        <option value="{{$provider->id}}">{{$provider->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="providers_list">
                            </div>




                            <div class="form-group">
                                <label for="select2" data-original-title="" title="" data-toggle="popover"
                                       data-placement="top" data-trigger="hover" data-title="Выбор салона"
                                       data-content="Начните вводить название салона, когда появится список - выберите нужный салон">Салон</label>
                                <select id="select2" name="salon_id" class="form-control">
                                    <option></option>
                                </select>
                            </div>

                            <div class="form-group">
                                <table class="table table-hover" id="salonTable" style="display: none;">
                                    <thead>
                                    <tr>
                                        <th>Салоны</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody id="salon_table_insert">

                                    </tbody>
                                </table>
                            </div>



                            <div class="form-group">
                                <label>Описание <span class="symbol required"></span></label>
        						<textarea class="form-control" name="description" required></textarea>
                            </div>

                            <div class="form-group">
                                <label>Внутренний комментарий</label>
        						<input type="text" name="comment" class="form-control" maxlength="250">
                            </div>

                            <div class="col-md-2 col-md-offset-4">
                                <input class="btn btn-primary btn-wide pull-center" type="submit" value="Сохранить">
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        {{-- </div> --}}

@endsection

@section('scripts')
    <script src="/vendor/select2/select2.min.js"></script>
    <script src="/vendor/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>

    <script type="text/javascript">
        var rows_count = 1;

        function addCharBlock(name, id) {
            var template = "<div class=\"panel panel-white no-radius\">\n <div class=\"panel-heading\">\n <h5 class=\"panel-title char-title\">" + name + "</h5>\n <div class=\"panel-tools\">\n\t\t\t\t\t\t<a data-original-title=\"Удалить\" data-toggle=\"tooltip\" data-placement=\"top\"\n   class=\"btn btn-transparent btn-sm panel-close\" href=\"javascript:void(0);\">\n <i class=\"ti-close drop-char\" data-id=\"" + id + "\" data-val=\"" + name + "\"></i>\n   </a>\n\t\t\t\t\t</div>\n </div>\n <div class=\"panel-body\">\n <div class=\"form-group\">\n   <label>Значение <span class=\"symbol required\"></span></label>\n   <input class=\"form-control input-sm\" name=\"characteristics[]\" type=\"text\">\n   <input name=\"characteristics_id[]\" value=\"" + id + "\" type=\"hidden\">\n </div>\n </div>\n  </div>\n  ";
            $('#append_char').append(template);
        }

        function addProviderBox(name, id) {
            var template = "\n    <div class=\"panel panel-white no-radius\">\n <div class=\"panel-heading\">\n   <h5 class=\"panel-title char-title\">" + name + "</h5>\n   <div class=\"panel-tools\">\n <a data-original-title=\"Удалить\" data-toggle=\"tooltip\" data-placement=\"top\"\n class=\"btn btn-transparent btn-sm panel-close\" href=\"javascript:void(0);\">\n <i class=\"ti-close drop-provider\" data-id=\"" + id + "\" data-val=\"" + name + "\"></i>\n </a>\n   </div>\n </div>\n <div class=\"panel-body\">\n   <div class=\"form-group\">\n <div class=\"col-md-12\">\n <label>Оригинальная цена <span class=\"symbol required\"></span></label>\n </div>\n <div class=\"col-md-8\">\n <input class=\"form-control input-sm\" name=\"provider_original_price[]\" type=\"text\"\n pattern=\"\\d*\\.?\\d{0,2}\" title=\"Допускаются только числовые значения с разделителем '.' (точка)\">\n </div>\n <div class=\"col-md-4\">\n <select class=\"form-control input-sm\" name=\"provider_currency[]\">\n <option value=\"UAH\">UAH</option>\n <option value=\"USD\">USD</option>\n <option value=\"EU\">EU</option>\n </select>\n </div>\n <div class=\"col-md-12\">\n <label>Код поставщика</label>\n <input class=\"form-control input-sm\" name=\"provider_code[]\" type=\"text\">\n </div>\n   </div>\n   <input name=\"provider_id[]\" value=\"" + id + "\" type=\"hidden\">\n </div>\n    </div>\n    ";
            $('#providers_list').append(template);
        }

        $(document).ready(function () {

            $('skip_models').val('');

            function formatRepo (repo) {
                return repo.name;
            }

            function formatRepoSelection (repo) {
                return repo.name || repo.id;
            }

            $("#price").TouchSpin({
                verticalbuttons: true,
                decimals: 2,
                min: 1,
                max: 99000000,
                stepinterval: 100,
                mousewheel: false,
                step: 0.01,
                maxboostedstep: 1000,
                initval: "1"
            });

            $("#count").TouchSpin({
                verticalbuttons: true,
                decimals: 0,
                min: 0,
                max: 99000000,
                stepinterval: 100,
                mousewheel: false,
                step: 1,
                maxboostedstep: 50,
                initval: "0"
            });

            $('#category_select').select2({
                placeholder: 'Начните вводить название родительской категории',
                allowClear: true,
                ajax: {
                    url: "{{ url('/').'/admin/categories/create/find'}}",
                    dataType: 'json',
                    delay: 400,
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 30) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
                minimumInputLength: 2,
                templateResult: formatRepo,
                templateSelection: formatRepoSelection
            });

            $("#manufacturers").select2({
                placeholder: "Выберите производителя"
            });

            $("#characteristic_id").select2({
                placeholder: "Выберите несколько характеристик"
            }).on('select2:select', function(e) {
                addCharBlock(e.params.data.text, e.params.data.id);
                $("#characteristic_id option[value='" + e.params.data.id + "']").remove();
                $("#characteristic_id").each(function(){
                    $(this).val('').change();
                });
                $('.drop-char').each(function() {
                    $(this).off('click').on('click', function(e) {
                        var that = $(this);
                        var val = that.attr("data-id");
                        var txt = that.attr("data-val");
                        $("#characteristic_id").append('<option value="' + val + '">' + txt + '</option>').select2({
                            placeholder: "Выберите несколько характеристик"
                        });
                        that.closest(".panel-white").detach();
                    });
                });
            });

            $('#providers').select2({
                placeholder: 'Выберите поставщиков'
            }).on('select2:select', function(e) {
                addProviderBox(e.params.data.text, e.params.data.id);
                $("#providers option[value='" + e.params.data.id + "']").remove();
                $("#providers").each(function(){
                    $(this).val('').change();
                });
                $('.drop-provider').each(function() {
                    $(this).off('click').on('click', function(e) {
                        var that = $(this);
                        var val = that.attr("data-id");
                        var txt = that.attr("data-val");
                        $("#providers").append('<option value="' + val + '">' + txt + '</option>').select2({
                            placeholder: "Выберите поставщика"
                        });
                        that.closest(".panel-white").detach();
                    });
                });
                $("#providers").select2({placeholder: 'Выберите поставщиков'});
            });

            $("#brand_id").select2({
                placeholder: "Выберите марку"
            }).on('change', function() {
                var escape_models = "";
                $.each($('.models_id_a'), function(i, val) {
                    escape_models += i == 0 ? val.value : ',' + val.value;
                });
                //console.info(escape_models);
                $.ajax({
                    url: "{{ url('/admin/ajax/models-by-brand') }}",
                    type: "post",
                    dataType: "html",
                    data: "brand_id=" + $("#brand_id").val() + "&escape=" + escape_models + "&_token={{csrf_token()}}",
                    success: function(result)
                    {
                        var models = $.parseJSON(result);
                        $("#models_select").show();
                        $("#model_id").empty().append('<option></option>');
                        $.each(models.data, function(i, item) {
                            $("#model_id").append(
                                '<option value="'+item.id+'" name="model_id">' + item.model_name + '</option>'
                            ).select2({placeholder: "Выберете модель"}).off('select2:select')
                            .on('select2:select', function(e) {
                                var template = "<tr>\n<td class=\"hidden-xs\">" + e.params.data.text + "</td>\n<td class=\"center\">\n    <div class=\"visible-md visible-lg hidden-sm hidden-xs\">\n        <input class=\"models_id_a\" type=\"hidden\" name=\"model_id[]\" value=\"" + e.params.data.id + "\">\n        <a href=\"javascript:void(0);\" class=\"btn btn-transparent btn-xs tooltips drop_model\"\ntooltip-placement=\"top\" tooltip=\"Удалить\">\n<i class=\"fa fa-times fa fa-white\"></i>\n        </a>\n    </div>\n</td>\n        </tr>";
                                $('#auto_models_table').show(700);
                                var tableModels = $('#models_table');
                                tableModels.append(template).promise().done(function() {
                                    $('.drop_model').off('click').on('click', function() {
                                        var that = $(this);
                                        that.closest('tr').fadeOut(600).promise()
                                            .done(function() {
                                                that.closest('tr').detach();
                                                if(!$('#models_table').has('tr').length)
                                                    $('#auto_models_table').hide(500);
                                            });
                                    });
                                    $("#model_id option[value='" + e.params.data.id + "']").remove();
                                    $("#model_id").each(function() {
                                        $(this).val('').change();
                                    });
                                });
                            });
                        });
                    }
                });
            });


            $('#select2').select2({

                placeholder: 'Начните вводить название салона',
                allowClear: true,
                ajax: {
                    url: "{{ url('/').'/admin/ajax/salons-search'}}",
                    dataType: 'json',
                    delay: 700,
                    data: function (params) {

                        var escape_salons = '';
                        $.each($('.salon_id_a'), function(i, val) {
                            escape_salons += i == 0 ? val.value : ',' + val.value;
                        });


                        return {
                            q: params.term,
                            page: params.page,
                            escape: escape_salons
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 30) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                escapeMarkup: function (markup) { return markup; },
                minimumInputLength: 2,
                templateResult: formatRepo,
                templateSelection: formatRepoSelection

                }).on('select2:select', function(e) {

                $("#select2 option[value='" + e.params.data.id + "']").remove();
                $("#select2").each(function() {
                    $(this).val('').change();
                });

                var template = "<tr>\n<td class=\"hidden-xs\">" + e.params.data.name + "</td>\n<td class=\"center\">\n  " +
                        "  <div class=\"visible-md visible-lg hidden-sm hidden-xs\">\n        " +
                        "<input class=\"salon_id_a\" type=\"hidden\" name=\"salon_id[]\" value=\"" + e.params.data.id + "\">\n        " +
                        "<a href=\"javascript:void(0);\" class=\"btn btn-transparent btn-xs tooltips drop_model\"\ntooltip-placement=\"top\" " +
                        "tooltip=\"Удалить\">\n<i " + "class=\"fa fa-times fa fa-white\"></i>\n        </a>\n    </div>\n</td>\n        </tr>";

                $('#salonTable').show(700);
                var tableSalon = $('#salon_table_insert');
                tableSalon.append(template).promise().done(function() {
                    $('.drop_model').off('click').on('click', function() {
                        var that = $(this);
                        that.closest('tr').fadeOut(600).promise()
                                .done(function() {
                                    that.closest('tr').detach();
                                    if(!$('#salon_table_insert').has('tr').length)
                                        $('#salonTable').hide(500);
                                });
                    });

                });


            });


        });


    </script>
@endsection

{{--
var template = `
<div class="panel panel-white no-radius">
    <div class="panel-heading">
        <h5 class="panel-title char-title">${name}</h5>
        <div class="panel-tools">
            <a data-original-title="Close" data-toggle="tooltip" data-placement="top"
            class="btn btn-transparent btn-sm panel-close" href="javascript:void(0);">
                <i class="ti-close drop-char" data-id="${id}" data-val="${name}"></i>
            </a>
        </div>
    </div>
    <div class="panel-body">
        <div class="form-group">
            <label>Значение <span class="symbol required"></span></label>
            <input class="form-control input-sm" name="characteristics[]" type="text">
            <input name="characteristics_id[]" value="${id}" type="hidden">
        </div>
    </div>
</div>
`;
 --}}


 {{--
 <div class="panel panel-white no-radius">
     <div class="panel-heading">
         <h5 class="panel-title char-title">${name}</h5>
         <div class="panel-tools">
             <a data-original-title="Удалить" data-toggle="tooltip" data-placement="top"
             class="btn btn-transparent btn-sm panel-close" href="javascript:void(0);">
                 <i class="ti-close drop-provider" data-id="${id}" data-val="${name}"></i>
             </a>
         </div>
     </div>
     <div class="panel-body">
         <div class="form-group">
             <div class="col-md-12">
                 <label>Оригинальная цена <span class="symbol required"></span></label>
             </div>
             <div class="col-md-8">
                 <input class="form-control input-sm" name="provider_original_price[]" type="text"
                 pattern="\d*\.?\d{0,2}" title="Допускаются только числовые значения с разделителем '.' (точка)">
             </div>
             <div class="col-md-4">
                 <select class="form-control input-sm" name="provider_currency[]">
                     <option value="грн.">UAH</option>
                     <option value="USD">USD</option>
                     <option value="EU">EU</option>
                 </select>
             </div>
             <div class="col-md-12">
                 <label>Код поставщика</label>
                 <input class="form-control input-sm" name="provider_code[]" type="text">
             </div>
         </div>
         <input name="provider_id[]" value="${id}" type="hidden">
     </div>
 </div>
  --}}

  {{--

  <tr>
      <td class="hidden-xs">Google Chrome</td>
      <td class="center">
          <div class="visible-md visible-lg hidden-sm hidden-xs">
              <input type="hidden" name="model_id[]" value="">
              <a href="javascript:void(0);" class="btn btn-transparent btn-xs tooltips"
                  tooltip-placement="top" tooltip="Убрать">
                  <i class="fa fa-times fa fa-white"></i>
              </a>
          </div>
      </td>
  </tr>

   --}}
