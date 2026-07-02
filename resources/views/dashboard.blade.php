@extends('layouts/default')
{{-- Page title --}}
@section('title')
{{ trans('general.dashboard') }}
@parent
@stop


{{-- Page content --}}
@section('content')

@if ($snipeSettings->dashboard_message!='')
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        {!!  Helper::parseEscapedMarkedown($snipeSettings->dashboard_message)  !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row modern-stats">

    <!-- assets -->
    <div class="col-lg-2 col-sm-4 col-xs-6">
        <a href="{{ route('hardware.index') }}" class="stat-card stat-assets">
            <i class="stat-icon fa-solid fa-laptop" aria-hidden="true"></i>
            <div class="stat-value">{{ number_format(\App\Models\Asset::AssetsForShow()->count()) }}</div>
            <div class="stat-label">{{ trans('general.assets') }}</div>
            <span class="stat-foot">
                {{ trans('general.view_all') }}
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </span>
        </a>
    </div><!-- ./col -->

    <!-- licenses -->
    <div class="col-lg-2 col-sm-4 col-xs-6">
        <a href="{{ route('licenses.index') }}" class="stat-card stat-licenses">
            <i class="stat-icon fa-solid fa-certificate" aria-hidden="true"></i>
            <div class="stat-value">{{ number_format($counts['license']) }}</div>
            <div class="stat-label">{{ trans('general.licenses') }}</div>
            <span class="stat-foot">
                {{ trans('general.view_all') }}
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </span>
        </a>
    </div><!-- ./col -->

    <!-- accessories -->
    <div class="col-lg-2 col-sm-4 col-xs-6">
        <a href="{{ route('accessories.index') }}" class="stat-card stat-accessories">
            <i class="stat-icon fa-solid fa-keyboard" aria-hidden="true"></i>
            <div class="stat-value">{{ number_format($counts['accessory']) }}</div>
            <div class="stat-label">{{ trans('general.accessories') }}</div>
            <span class="stat-foot">
                {{ trans('general.view_all') }}
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </span>
        </a>
    </div><!-- ./col -->

    <!-- consumables -->
    <div class="col-lg-2 col-sm-4 col-xs-6">
        <a href="{{ route('consumables.index') }}" class="stat-card stat-consumables">
            <i class="stat-icon fa-solid fa-droplet" aria-hidden="true"></i>
            <div class="stat-value">{{ number_format($counts['consumable']) }}</div>
            <div class="stat-label">{{ trans('general.consumables') }}</div>
            <span class="stat-foot">
                {{ trans('general.view_all') }}
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </span>
        </a>
    </div><!-- ./col -->

    <!-- components -->
    <div class="col-lg-2 col-sm-4 col-xs-6">
        <a href="{{ route('components.index') }}" class="stat-card stat-components">
            <i class="stat-icon fa-solid fa-microchip" aria-hidden="true"></i>
            <div class="stat-value">{{ number_format($counts['component']) }}</div>
            <div class="stat-label">{{ trans('general.components') }}</div>
            <span class="stat-foot">
                {{ trans('general.view_all') }}
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </span>
        </a>
    </div><!-- ./col -->

    <!-- people -->
    <div class="col-lg-2 col-sm-4 col-xs-6">
        <a href="{{ route('users.index') }}" class="stat-card stat-people">
            <i class="stat-icon fa-solid fa-user-group" aria-hidden="true"></i>
            <div class="stat-value">{{ number_format($counts['user']) }}</div>
            <div class="stat-label">{{ trans('general.people') }}</div>
            <span class="stat-foot">
                {{ trans('general.view_all') }}
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </span>
        </a>
    </div><!-- ./col -->
</div>

@if ($counts['grand_total'] == 0)

    <div class="row">

        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">{{ trans('general.dashboard_info') }}</h2>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">

                            <div class="progress">
                                <div class="progress-bar progress-bar-yellow" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%">
                                    <span class="sr-only">{{ trans('general.60_percent_warning') }}</span>
                                </div>
                            </div>


                            <p><strong>{{ trans('general.dashboard_empty') }}</strong></p>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            @can('create', \App\Models\Asset::class)
                            <a class="btn bg-teal" style="width: 100%" href="{{ route('hardware.create') }}">{{ trans('general.new_asset') }}</a>
                            @endcan
                        </div>
                        <div class="col-md-2">
                            @can('create', \App\Models\License::class)
                                <a class="btn bg-maroon" style="width: 100%" href="{{ route('licenses.create') }}">{{ trans('general.new_license') }}</a>
                            @endcan
                        </div>
                        <div class="col-md-2">
                            @can('create', \App\Models\Accessory::class)
                                <a class="btn bg-orange" style="width: 100%" href="{{ route('accessories.create') }}">{{ trans('general.new_accessory') }}</a>
                            @endcan
                        </div>
                        <div class="col-md-2">
                            @can('create', \App\Models\Consumable::class)
                                <a class="btn bg-purple" style="width: 100%" href="{{ route('consumables.create') }}">{{ trans('general.new_consumable') }}</a>
                            @endcan
                        </div>
                        <div class="col-md-2">
                            @can('create', \App\Models\Component::class)
                                <a class="btn bg-yellow" style="width: 100%" href="{{ route('components.create') }}">{{ trans('general.new_component') }}</a>
                            @endcan
                        </div>
                        <div class="col-md-2">
                            @can('create', \App\Models\User::class)
                                <a class="btn bg-light-blue" style="width: 100%" href="{{ route('users.create') }}">{{ trans('general.new_user') }}</a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@else

<!-- recent activity -->
<div class="row">
  <div class="col-md-8">
    <div class="box box-default">
      <div class="box-header with-border">
        <h2 class="box-title">{{ trans('general.recent_activity') }}</h2>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                <x-icon type="minus" />
                <span class="sr-only">{{ trans('general.collapse') }}</span>
            </button>
        </div>
      </div><!-- /.box-header -->
      <div class="box-body">
        <div class="row">
          <div class="col-md-12">

                <table
                    data-cookie-id-table="dashActivityReport"
                    data-height="500"
                    data-pagination="false"
                    data-side-pagination="server"
                    data-id-table="dashActivityReport"
                    data-sort-order="desc"
                    data-show-columns="false"
                    data-fixed-number="false"
                    data-fixed-right-number="false"
                    data-sort-name="created_at"
                    id="dashActivityReport"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.activity.index', ['limit' => 25]) }}">
                    <thead>
                    <tr>
                        <th data-field="icon" data-visible="true" style="width: 40px;" class="hidden-xs" data-formatter="iconFormatter"><span  class="sr-only">{{ trans('admin/hardware/table.icon') }}</span></th>
                        <th class="col-sm-3" data-visible="true" data-field="created_at" data-formatter="dateDisplayFormatter">{{ trans('general.date') }}</th>
                        <th class="col-sm-2" data-visible="true" data-field="admin" data-formatter="usersLinkObjFormatter">{{ trans('general.created_by') }}</th>
                        <th class="col-sm-2" data-visible="true" data-field="action_type">{{ trans('general.action') }}</th>
                        <th class="col-sm-3" data-visible="true" data-field="item" data-formatter="polymorphicItemFormatter">{{ trans('general.item') }}</th>
                        <th class="col-sm-2" data-visible="true" data-field="target" data-formatter="polymorphicItemFormatter">{{ trans('general.target') }}</th>
                    </tr>
                    </thead>
                </table>
          </div><!-- /.col -->
          <div class="text-center col-md-12" style="padding-top: 10px;">
            <a href="{{ route('reports.activity') }}" class="btn btn-theme btn-sm" style="width: 100%">{{ trans('general.viewall') }}</a>
          </div>
        </div><!-- /.row -->
      </div><!-- ./box-body -->
    </div><!-- /.box -->
  </div>
  <div class="col-md-4">
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">
                    {{ (\App\Models\Setting::getSettings()->dash_chart_type == 'name') ? trans('general.assets_by_status') : trans('general.assets_by_status_type') }}
                </h2>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                        <x-icon type="minus" />
                        <span class="sr-only">{{ trans('general.collapse') }}</span>
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="chart-responsive">
                            <canvas id="statusPieChart" height="260"></canvas>
                        </div> <!-- ./chart-responsive -->
                    </div> <!-- /.col -->
                </div> <!-- /.row -->
            </div><!-- /.box-body -->
        </div> <!-- /.box -->
  </div>

</div> <!--/row-->
<div class="row">
    <div class="col-md-6">

		@if ((($snipeSettings->scope_locations_fmcs!='1') && ($snipeSettings->full_multiple_companies_support=='1')))
			 <!-- Companies -->	
			<div class="box box-default">
				<div class="box-header with-border">
					<h2 class="box-title">{{ trans('general.companies') }}</h2>
					<div class="box-tools pull-right">
						<button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <x-icon type="minus" />
							<span class="sr-only">{{ trans('general.collapse') }}</span>
						</button>
					</div>
				</div>
				<!-- /.box-header -->
				<div class="box-body">
					<div class="row">
						<div class="col-md-12">
							<table
									data-cookie-id-table="dashCompanySummary"
									data-height="400"
                                    data-pagination="false"
									data-side-pagination="server"
									data-sort-order="desc"
                                    data-show-columns="false"
                                    data-fixed-number="false"
                                    data-fixed-right-number="false"
									data-sort-field="assets_count"
									id="dashCompanySummary"
									class="table table-striped snipe-table"
									data-url="{{ route('api.companies.index', ['sort' => 'assets_count', 'order' => 'asc']) }}">

								<thead>
								<tr>
									<th class="col-sm-3" data-visible="true" data-field="name" data-formatter="companiesLinkFormatter" data-sortable="true">{{ trans('general.name') }}</th>
									<th class="col-sm-1" data-visible="true" data-field="users_count" data-sortable="true">
                                        <x-icon type="users" />
										<span class="sr-only">{{ trans('general.people') }}</span>
									</th>
									<th class="col-sm-1" data-visible="true" data-field="assets_count" data-sortable="true">
                                        <x-icon type="assets" />
										<span class="sr-only">{{ trans('general.asset_count') }}</span>
									</th>
									<th class="col-sm-1" data-visible="true" data-field="accessories_count" data-sortable="true">
                                        <x-icon type="accessories" />
										<span class="sr-only">{{ trans('general.accessories_count') }}</span>
									</th>
									<th class="col-sm-1" data-visible="true" data-field="consumables_count" data-sortable="true">
                                        <x-icon type="consumables" />
										<span class="sr-only">{{ trans('general.consumables_count') }}</span>
									</th>
									<th class="col-sm-1" data-visible="true" data-field="components_count" data-sortable="true">
                                        <x-icon type="components" />
										<span class="sr-only">{{ trans('general.components_count') }}</span>
									</th>
									<th class="col-sm-1" data-visible="true" data-field="licenses_count" data-sortable="true">
                                        <x-icon type="licenses" />
										<span class="sr-only">{{ trans('general.licenses_count') }}</span>
									</th>
								</tr>
								</thead>
							</table>
						</div> <!-- /.col -->
						<div class="text-center col-md-12" style="padding-top: 10px;">
							<a href="{{ route('companies.index') }}" class="btn btn-theme btn-sm" style="width: 100%">{{ trans('general.viewall') }}</a>
						</div>
					</div> <!-- /.row -->

				</div><!-- /.box-body -->
			</div> <!-- /.box -->
		
		@else
			 <!-- Locations -->
			 <div class="box box-default">
				<div class="box-header with-border">
					<h2 class="box-title">{{ trans('general.locations') }}</h2>
					<div class="box-tools pull-right">
						<button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <x-icon type="minus" />
							<span class="sr-only">{{ trans('general.collapse') }}</span>
						</button>
					</div>
				</div>
				<!-- /.box-header -->
				<div class="box-body">
					<div class="row">
						<div class="col-md-12">

							<table
									data-cookie-id-table="dashLocationSummary"
									data-height="400"
									data-side-pagination="server"
                                    data-pagination="false"
									data-sort-order="desc"
                                    data-fixed-number="false"
                                    data-fixed-right-number="false"
									data-sort-field="assets_count"
									id="dashLocationSummary"
                                    data-show-columns="false"
									class="table table-striped snipe-table"
									data-url="{{ route('api.locations.index', ['sort' => 'assets_count', 'order' => 'asc']) }}">
								<thead>
								<tr>
									<th class="col-sm-3" data-visible="true" data-field="name" data-formatter="locationsLinkFormatter" data-sortable="true">{{ trans('general.name') }}</th>
									
									<th class="col-sm-1" data-visible="true" data-field="assets_count" data-sortable="true">
                                        <x-icon type="assets" />
										<span class="sr-only">{{ trans('general.asset_count') }}</span>
									</th>
									<th class="col-sm-1" data-visible="true" data-field="assigned_assets_count" data-sortable="true">
										
										{{ trans('general.assigned') }}
									</th>
									<th class="col-sm-1" data-visible="true" data-field="users_count" data-sortable="true">
                                        <x-icon type="users" />
										<span class="sr-only">{{ trans('general.people') }}</span>
										
									</th>
									
								</tr>
								</thead>
							</table>
						</div> <!-- /.col -->
						<div class="text-center col-md-12" style="padding-top: 10px;">
							<a href="{{ route('locations.index') }}" class="btn btn-theme btn-sm" style="width: 100%">{{ trans('general.viewall') }}</a>
						</div>
					</div> <!-- /.row -->

				</div><!-- /.box-body -->
			</div> <!-- /.box -->

		@endif
			
    </div>
    <div class="col-md-6">

        <!-- Categories -->
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">{{ trans('general.asset') }} {{ trans('general.categories') }}</h2>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <x-icon type="minus" />
                        <span class="sr-only">{{ trans('general.collapse') }}</span>
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">

                        <table
                                data-cookie-id-table="dashCategorySummary"
                                data-height="400"
                                data-pagination="false"
                                data-side-pagination="server"
                                data-show-columns="false"
                                data-fixed-number="false"
                                data-fixed-right-number="false"
                                data-sort-order="desc"
                                data-sort-field="assets_count"
                                id="dashCategorySummary"
                                class="table table-striped snipe-table"
                                data-url="{{ route('api.categories.index', ['sort' => 'assets_count', 'order' => 'asc']) }}">
                            <thead>
                            <tr>
                                <th class="col-sm-3" data-visible="true" data-field="name" data-formatter="categoriesLinkFormatter" data-sortable="true">{{ trans('general.name') }}</th>
                                <th class="col-sm-3" data-visible="true" data-field="category_type" data-sortable="true">
                                    {{ trans('general.type') }}
                                </th>
                                <th class="col-sm-1" data-visible="true" data-field="assets_count" data-sortable="true">
                                    <x-icon type="assets" />
                                    <span class="sr-only">{{ trans('general.asset_count') }}</span>
                                </th>
                                <th class="col-sm-1" data-visible="true" data-field="accessories_count" data-sortable="true">
                                    <x-icon type="licenses" />
                                    <span class="sr-only">{{ trans('general.accessories_count') }}</span>
                                </th>
                                <th class="col-sm-1" data-visible="true" data-field="consumables_count" data-sortable="true">
                                    <x-icon type="consumables" />
                                    <span class="sr-only">{{ trans('general.consumables_count') }}</span>
                                </th>
                                <th class="col-sm-1" data-visible="true" data-field="components_count" data-sortable="true">
                                    <x-icon type="components" />
                                    <span class="sr-only">{{ trans('general.components_count') }}</span>
                                </th>
                                <th class="col-sm-1" data-visible="true" data-field="licenses_count" data-sortable="true">
                                    <x-icon type="licenses" />
                                    <span class="sr-only">{{ trans('general.licenses_count') }}</span>
                                </th>
                            </tr>
                            </thead>
                        </table>

                    </div> <!-- /.col -->
                    <div class="text-center col-md-12" style="padding-top: 10px;">
                        <a href="{{ route('categories.index') }}" class="btn btn-theme btn-sm" style="width: 100%">{{ trans('general.viewall') }}</a>
                    </div>
                </div> <!-- /.row -->

            </div><!-- /.box-body -->
        </div> <!-- /.box -->
    </div>


@endif


@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['simple_view' => true, 'nopages' => true])
@stop

@push('css')
<style>
/* ---- Modern dashboard stat cards ---- */
.modern-stats { margin-bottom: 6px; }

.stat-card {
    position: relative;
    display: block;
    min-height: 128px;
    padding: 20px 20px 46px;
    border-radius: 16px;
    color: #fff;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(17, 24, 39, .12);
    transition: transform .18s ease, box-shadow .18s ease;
}
.stat-card:hover,
.stat-card:focus {
    color: #fff;
    text-decoration: none;
    transform: translateY(-5px);
    box-shadow: 0 14px 28px rgba(17, 24, 39, .22);
}

.stat-card .stat-value {
    font-size: 34px;
    font-weight: 700;
    line-height: 1.05;
    margin-bottom: 2px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, .18);
}
.stat-card .stat-value,
.stat-card .stat-label,
.stat-card .stat-icon,
.stat-card .stat-foot {
    color: #fff;
}
.stat-card .stat-label {
    font-size: 14px;
    font-weight: 600;
    letter-spacing: .2px;
    opacity: 1;
}
.stat-card .stat-icon {
    position: absolute;
    top: 16px;
    right: 18px;
    font-size: 40px;
    opacity: 1;
    transition: transform .18s ease, opacity .18s ease;
}
.stat-card:hover .stat-icon {
    transform: scale(1.12) rotate(-6deg);
}
.stat-card .stat-foot {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    padding: 8px 18px;
    font-size: 12px;
    font-weight: 600;
    background: rgba(0, 0, 0, .14);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.stat-card .stat-foot .fa-arrow-right { transition: transform .18s ease; }
.stat-card:hover .stat-foot .fa-arrow-right { transform: translateX(4px); }

/* Gray gradient palette (dark graphite -> slate) */
.stat-assets      { background: linear-gradient(135deg, #2c333e 0%, #3b4552 100%); }
.stat-licenses    { background: linear-gradient(135deg, #343c47 0%, #454f5e 100%); }
.stat-accessories { background: linear-gradient(135deg, #3c4450 0%, #4d586a 100%); }
.stat-consumables { background: linear-gradient(135deg, #454e5a 0%, #566475 100%); }
.stat-components  { background: linear-gradient(135deg, #4d5764 0%, #5f6d7f 100%); }
.stat-people      { background: linear-gradient(135deg, #566070 0%, #6a7a8c 100%); }

/* Softer, rounded boxes for the rest of the dashboard */
.content-wrapper .box.box-default {
    border: none;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(17, 24, 39, .08);
    border-top: none;
}
.content-wrapper .box.box-default > .box-header.with-border {
    border-bottom: 1px solid rgba(0, 0, 0, .06);
    border-top-left-radius: 14px;
    border-top-right-radius: 14px;
}

/* Dark mode */
[data-theme="dark"] .stat-card { box-shadow: 0 6px 18px rgba(0, 0, 0, .45); }
[data-theme="dark"] .content-wrapper .box.box-default { box-shadow: 0 4px 14px rgba(0, 0, 0, .40); }
[data-theme="dark"] .content-wrapper .box.box-default > .box-header.with-border { border-bottom-color: rgba(255, 255, 255, .08); }

@media (max-width: 767px) {
    .stat-card { min-height: 116px; }
    .stat-card .stat-value { font-size: 28px; }
    .stat-card .stat-icon { font-size: 32px; }
}
</style>
@endpush

@push('js')


        <script src="{{ url(mix('js/dist/Chart.min.js')) }}"></script>
<script nonce="{{ csrf_token() }}">
    // ---------------------------
    // - ASSET STATUS CHART -
    // ---------------------------
      var pieChartCanvas = $("#statusPieChart").get(0).getContext("2d");
      var pieChart = new Chart(pieChartCanvas);
      var ctx = document.getElementById("statusPieChart");
      var pieOptions = {
              legend: {
                  position: 'top',
                  responsive: true,
                  maintainAspectRatio: true,
              },
              tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        counts = data.datasets[0].data;
                        total = 0;
                        for(var i in counts) {
                            total += counts[i];
                        }
                        prefix = data.labels[tooltipItem.index] || '';
                        return prefix+" "+Math.round(counts[tooltipItem.index]/total*100)+"%";
                    }
                }
              }
          };

      $.ajax({
          type: 'GET',
          url: '{{ (\App\Models\Setting::getSettings()->dash_chart_type == 'name') ? route('api.statuslabels.assets.byname') : route('api.statuslabels.assets.bytype') }}',
          headers: {
              "X-Requested-With": 'XMLHttpRequest',
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
          },
          dataType: 'json',
          success: function (data) {
              var myPieChart = new Chart(ctx,{
                  type   : 'pie',
                  data   : data,
                  options: pieOptions
              });
          },
          error: function (data) {
              // window.location.reload(true);
          },
      });
        var last = document.getElementById('statusPieChart').clientWidth;
        addEventListener('resize', function() {
        var current = document.getElementById('statusPieChart').clientWidth;
        if (current != last) location.reload();
        last = current;
    });
</script>
@endpush
