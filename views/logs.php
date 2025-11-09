<?php
$pageTitle = "Logs - IMS";
require_once __DIR__ . '/../helpers/auth.php';
require_role(['admin','manager']);
include __DIR__ . '/../global/header.php';
include __DIR__ . '/../global/sidebar.php';
?>

<link href="<?= $appUrl ?>/public/css/logs.css" rel="stylesheet" />

<main class="content p-3 p-md-4">
	<div class="d-flex align-items-center justify-content-between mb-3">
		<h4 class="mb-0">System Logs</h4>
	</div>
<div class="card mb-3">
	<div class="card-body">
		<form id="logsFilterForm" class="row g-3 align-items-end">
			<div class="col-12 col-md-4 col-lg-3">
				<label class="form-label">Date Range</label>
				<input type="text" class="form-control" id="dateRange" name="date_range" autocomplete="off" placeholder="Select range" />
			</div>

			<div class="col-12 col-md-3 col-lg-2">
				<label class="form-label">Action</label>
				<select class="form-select" name="log_action">
					<option value="">All</option>
					<option>login_success</option>
					<option>login_failed</option>
					<option>logout</option>
					<option>create</option>
					<option>update</option>
					<option>archive</option>
					<option>delete</option>
					<option>view</option>
				</select>
			</div>

			<div class="col-12 col-md-5 col-lg-4 d-flex">
				<button type="submit" class="btn btn-primary me-2">
					<i class="bi bi-search me-1"></i> Filter
				</button>
				<button type="button" id="resetFilters" class="btn btn-light">
					<i class="bi bi-arrow-clockwise me-1"></i> Reset
				</button>
			</div>
		</form>
	</div>
</div>

	<div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table id="logsTable" class="table table-striped table-hover align-middle" style="width:100%">
					<thead>
						<tr>
							<th>Time</th>
							<th>User</th>
							<th>Action</th>
							<th>Entity</th>
							<th>Entity ID</th>
							<th>IP</th>
							<th>User Agent</th>
							<th>Description</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<style>
/* Align DataTables search to right */
div.dataTables_filter { text-align: right !important; }
div.dataTables_filter label { width: 100%; }
div.dataTables_filter input { margin-left: .5rem; }
/* Narrow description column wrap */
table#logsTable td { vertical-align: middle; }
</style>
<script>
	const appUrl = <?= json_encode($appUrl) ?>;
	let logsTable;
	$(function(){

		// Initialize date range picker with current month default
		const startOfMonth = moment().startOf('month');
		const endOfMonth = moment().endOf('month');
		$('#dateRange').daterangepicker({
			startDate: startOfMonth,
			endDate: endOfMonth,
			locale: { format: 'YYYY-MM-DD' },
			autoUpdateInput: true,
			opens: 'left',
			ranges: {
				'Today': [moment(), moment()],
				'Yesterday': [moment().subtract(1,'days'), moment().subtract(1,'days')],
				'Last 7 Days': [moment().subtract(6,'days'), moment()],
				'Last 30 Days': [moment().subtract(29,'days'), moment()],
				'This Month': [moment().startOf('month'), moment().endOf('month')],
				'Last Month': [moment().subtract(1,'month').startOf('month'), moment().subtract(1,'month').endOf('month')]
			}
		});
		// Ensure input shows value
		$('#dateRange').val(startOfMonth.format('YYYY-MM-DD') + ' - ' + endOfMonth.format('YYYY-MM-DD'));

		function currentFilters(){
			const params = { action: 'list_logs' };
			const dr = $('#dateRange').val();
			if (dr && dr.includes(' - ')) {
				const parts = dr.split(' - ');
				params.date_from = parts[0];
				params.date_to = parts[1];
			}
			const formEl = document.getElementById('logsFilterForm');
			const fd = new FormData(formEl);
			for (const [k,v] of fd.entries()) {
				if (k === 'date_range') continue; // handled
				if (v) params[k] = v;
			}
			return params;
		}

		function showFilterToast(){
			Swal.fire({
				toast: true,
				icon: 'success',
				title: 'Filters applied',
				position: 'top-end',
				showConfirmButton: false,
				timer: 1500
			});
		}

		logsTable = $('#logsTable').DataTable({
			ajax: {
				url: appUrl + '/backend/logs/unified_logs_management.php',
				data: function(){ return currentFilters(); },
				dataSrc: function(json){ return (json && json.data && json.data.items) ? json.data.items : []; }
			},
			pageLength: 25,
			order: [[0,'desc']],
			dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
				"<'row'<'col-sm-12'tr>>" +
				"<'row'<'col-sm-5'i><'col-sm-7'p>>",
			columns: [
				{ data: 'created_at', render: d => new Date(d).toLocaleString() },
				{ data: 'full_name', render: d => d || '-' },
				{ data: 'action' },
				{ data: 'entity' },
				{ data: 'entity_id', render: d => d || '' },
				{ data: 'ip_address' },
				{ data: 'user_agent', render: d => d ? `<span title="${d}">${d.substring(0,40)}${d.length>40?'…':''}</span>` : '' },
				{ data: 'description', render: d => {
					if (!d) return '';
					const str = String(d);
					return `<span title="${str.replaceAll('"','&quot;')}">${str.substring(0,60)}${str.length>60?'…':''}</span>`;
				}}
			]
		});

		$('#logsFilterForm').on('submit', function(e){
			e.preventDefault();
			logsTable.ajax.reload(showFilterToast);
		});

		$('#resetFilters').on('click', function(){
			// Reset form but keep current month default
			document.getElementById('logsFilterForm').reset();
			$('#dateRange').data('daterangepicker').setStartDate(startOfMonth);
			$('#dateRange').data('daterangepicker').setEndDate(endOfMonth);
			$('#dateRange').val(startOfMonth.format('YYYY-MM-DD') + ' - ' + endOfMonth.format('YYYY-MM-DD'));
			logsTable.ajax.reload(() => {
				Swal.fire({ toast:true, icon:'info', title:'Filters reset to current month', position:'top-end', showConfirmButton:false, timer:1500 });
			});
		});
	});
</script>

<?php include __DIR__ . '/../global/footer.php'; ?>
