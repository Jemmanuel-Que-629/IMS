<?php
$pageTitle = "Products - IMS";
require_once __DIR__ . '/../helpers/auth.php';
require_role(['admin','manager']);
// Header includes app config, fonts, CSS, opens layout wrapper and topbar
include __DIR__ . '/../global/header.php';
// Sidebar lives within the same layout wrapper opened by header
include __DIR__ . '/../global/sidebar.php';
?>
<!-- Page-specific styles -->
<link href="<?= $appUrl ?>/public/css/products.css" rel="stylesheet" />
		<main class="content p-3 p-md-4">
			<div class="products-header d-flex align-items-center justify-content-between">
				<h4 class="mb-0">Products</h4>
				<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
					<i class="bi bi-plus-lg me-1"></i> Add Product
				</button>
			</div>

			<!-- Consumables Table -->
			<div class="card mt-3">
				<div class="card-header bg-light">
					<strong>Consumables</strong>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table id="consumablesTable" class="table table-striped table-hover align-middle" style="width:100%">
							<thead>
								<tr>
									<th>Picture</th>
									<th>Name</th>
									<th>Quantity</th>
									<th>Price</th>
									<th>Created</th>
									<th class="text-end">Actions</th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
			</div>

			<!-- Non-Consumables Table -->
			<div class="card mt-4">
				<div class="card-header bg-light">
					<strong>Non-Consumables</strong>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table id="nonConsumablesTable" class="table table-striped table-hover align-middle" style="width:100%">
							<thead>
								<tr>
									<th>Picture</th>
									<th>Name</th>
									<th>Quantity</th>
									<th>Price</th>
									<th>Created</th>
									<th class="text-end">Actions</th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
			</div>
		</main>

	<!-- Add Product Modal -->
	<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<form id="addProductForm" enctype="multipart/form-data">
					<div class="modal-header">
						<h5 class="modal-title">Add Product</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<div class="row g-3">
							<div class="col-12">
								<label class="form-label">Name</label>
								<input type="text" name="name" class="form-control" required />
							</div>
							<div class="col-6">
								<label class="form-label">Quantity</label>
								<input type="number" name="quantity" class="form-control" min="0" value="0" required />
							</div>
							<div class="col-6">
								<label class="form-label">Price</label>
								<input type="number" step="0.01" name="price" class="form-control" min="0" value="0" required />
							</div>
							<div class="col-12">
								<label class="form-label">Category</label>
								<select name="category" class="form-select" required>
									<option value="" disabled selected>Select a category</option>
									<option value="consumables">Consumables</option>
									<option value="non_consumables">Non-consumables</option>
								</select>
							</div>
							<div class="col-12">
								<label class="form-label">Picture (optional)</label>
								<input type="file" name="picture" accept="image/*" class="form-control" />
								<div class="form-text">JPG, PNG, GIF, or WEBP. Max 5MB.</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary">Save</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		const appUrl = <?= json_encode($appUrl) ?>;

		let consumablesTable, nonConsumablesTable;
		$(function(){
			const baseAjax = {
				url: appUrl + '/backend/products/unified_products_management.php',
				data: { action: 'list_products' },
				dataSrc: function(json){
					return (json && json.data && json.data.items) ? json.data.items : [];
				}
			};

			consumablesTable = $('#consumablesTable').DataTable({
				ajax: Object.assign({}, baseAjax, { data: { action: 'list_products', category: 'consumables' } }),
				pageLength: 10,
				lengthChange: false,
				columns: tableColumns()
			});

			nonConsumablesTable = $('#nonConsumablesTable').DataTable({
				ajax: Object.assign({}, baseAjax, { data: { action: 'list_products', category: 'non_consumables' } }),
				pageLength: 10,
				lengthChange: false,
				columns: tableColumns()
			});

			function tableColumns(){
				return [
					{ data: 'picture', render: function(data){
							if (data) {
								const src = appUrl + data;
								return '<img class="product-thumb" src="'+src+'" alt="product" />';
							}
							return '<span class="badge bg-secondary">No image</span>';
						}, orderable: false, searchable: false },
					{ data: 'name' },
					{ data: 'quantity' },
					{ data: 'price', render: function(d){ return '₱ ' + parseFloat(d).toFixed(2); } },
					{ data: 'created_at', render: function(d){ return new Date(d).toLocaleString(); } },
					{ data: null, className: 'text-end', orderable: false, searchable: false, render: function(row){
						return `
						<div class="dropdown">
							<button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
								<i class="bi bi-three-dots-vertical"></i>
							</button>
							<ul class="dropdown-menu dropdown-menu-end">
								<li><a class="dropdown-item action-view" href="#" data-id="${row.product_id}"><i class="bi bi-eye me-2"></i>View</a></li>
								<li><a class="dropdown-item action-edit" href="#" data-id="${row.product_id}"><i class="bi bi-pencil-square me-2"></i>Edit</a></li>
								<li><hr class="dropdown-divider"/></li>
								<li><a class="dropdown-item text-warning action-archive" href="#" data-id="${row.product_id}"><i class="bi bi-archive me-2"></i>Archive</a></li>
								<li><a class="dropdown-item text-danger action-delete" href="#" data-id="${row.product_id}"><i class="bi bi-trash3 me-2"></i>Delete</a></li>
							</ul>
						</div>`;
					}}
				];
			}

			function reloadTables(){
				consumablesTable.ajax.reload(null, false);
				nonConsumablesTable.ajax.reload(null, false);
			}

			// Submit Add Product form via AJAX
			$('#addProductForm').on('submit', function(e){
				e.preventDefault();
				const fd = new FormData(this);
				fd.append('action', 'create_product');

				fetch(appUrl + '/backend/products/unified_products_management.php', {
					method: 'POST',
					body: fd
				})
				.then(r => r.json())
				.then(res => {
					if (res.success) {
						const modalEl = document.getElementById('addProductModal');
						bootstrap.Modal.getOrCreateInstance(modalEl).hide();
						e.target.reset();
						reloadTables();
						Swal.fire({ toast: true, icon: 'success', title: 'Product added successfully!', position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true });
					} else {
						Swal.fire({ icon: 'error', title: 'Failed', text: res.message || 'Unable to add product' });
					}
				})
				.catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message }));
			});

			// Actions: View/Edit/Archive/Delete
			$(document).on('click', '.action-view', function(e){
				e.preventDefault();
				const id = this.dataset.id;
				fetch(`${appUrl}/backend/products/unified_products_management.php?action=view_product&product_id=${id}`)
				.then(r => r.json()).then(res => {
					if (!res.success) return Swal.fire({ icon: 'error', title: 'Error', text: res.message });
					const p = res.data.item;
					$('#viewProductName').text(p.name);
					$('#viewProductCategory').text(p.category === 'consumables' ? 'Consumables' : 'Non-Consumables');
					$('#viewProductQty').text(p.quantity);
					$('#viewProductPrice').text('₱ ' + parseFloat(p.price).toFixed(2));
					$('#viewProductCreated').text(new Date(p.created_at).toLocaleString());
					$('#viewProductUpdated').text(new Date(p.updated_at).toLocaleString());
					if (p.picture) {
						$('#viewProductImage').attr('src', appUrl + p.picture).removeClass('d-none');
					} else {
						$('#viewProductImage').addClass('d-none');
					}
					bootstrap.Modal.getOrCreateInstance(document.getElementById('viewProductModal')).show();
				}).catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message }));
			});

			$(document).on('click', '.action-edit', function(e){
				e.preventDefault();
				const id = this.dataset.id;
				fetch(`${appUrl}/backend/products/unified_products_management.php?action=view_product&product_id=${id}`)
				.then(r => r.json()).then(res => {
					if (!res.success) return Swal.fire({ icon: 'error', title: 'Error', text: res.message });
					const p = res.data.item;
					$('#editProductId').val(p.product_id);
					$('#editProductName').val(p.name);
					$('#editProductQty').val(p.quantity);
					$('#editProductPrice').val(p.price);
					$('#editProductCategory').val(p.category);
					if (p.picture) {
						$('#editProductPreview').attr('src', appUrl + p.picture).removeClass('d-none');
					} else {
						$('#editProductPreview').addClass('d-none');
					}
					$('#editProductPicture').val('');
					bootstrap.Modal.getOrCreateInstance(document.getElementById('editProductModal')).show();
				}).catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message }));
			});

			$('#editProductForm').on('submit', function(e){
				e.preventDefault();
				const fd = new FormData(this);
				fd.append('action', 'update_product');
				fetch(appUrl + '/backend/products/unified_products_management.php', { method: 'POST', body: fd })
				.then(r => r.json()).then(res => {
					if (res.success) {
						bootstrap.Modal.getOrCreateInstance(document.getElementById('editProductModal')).hide();
						reloadTables();
						Swal.fire({ toast: true, icon: 'success', title: 'Product updated', position: 'top-end', showConfirmButton: false, timer: 1500 });
					} else {
						Swal.fire({ icon: 'error', title: 'Failed', text: res.message || 'Unable to update product' });
					}
				}).catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message }));
			});

			$(document).on('click', '.action-archive', function(e){
				e.preventDefault();
				const id = this.dataset.id;
				$('#archiveProductId').val(id);
				bootstrap.Modal.getOrCreateInstance(document.getElementById('archiveProductModal')).show();
			});
			$('#archiveProductForm').on('submit', function(e){
				e.preventDefault();
				const fd = new FormData(this);
				fd.append('action','archive_product');
				fetch(appUrl + '/backend/products/unified_products_management.php', { method: 'POST', body: fd })
					.then(r => r.json()).then(res => {
						if (res.success) {
							bootstrap.Modal.getOrCreateInstance(document.getElementById('archiveProductModal')).hide();
							reloadTables();
							Swal.fire({ toast: true, icon: 'success', title: 'Product archived', position: 'top-end', showConfirmButton: false, timer: 1500 });
						} else {
							Swal.fire({ icon: 'error', title: 'Failed', text: res.message || 'Unable to archive product' });
						}
					}).catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message }));
			});

			$(document).on('click', '.action-delete', function(e){
				e.preventDefault();
				const id = this.dataset.id;
				$('#deleteProductId').val(id);
				bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteProductModal')).show();
			});
			$('#deleteProductForm').on('submit', function(e){
				e.preventDefault();
				const fd = new FormData(this);
				fd.append('action','delete_product');
				fetch(appUrl + '/backend/products/unified_products_management.php', { method: 'POST', body: fd })
					.then(r => r.json()).then(res => {
						if (res.success) {
							bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteProductModal')).hide();
							reloadTables();
							Swal.fire({ toast: true, icon: 'success', title: 'Product deleted', position: 'top-end', showConfirmButton: false, timer: 1500 });
						} else {
							Swal.fire({ icon: 'error', title: 'Failed', text: res.message || 'Unable to delete product' });
						}
					}).catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message }));
			});
		});
	</script>

	<?php include __DIR__ . '/../global/footer.php'; ?>

	<!-- View Product Modal -->
	<div class="modal fade" id="viewProductModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Product Details</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="text-center mb-3">
						<img id="viewProductImage" class="img-fluid rounded d-none" style="max-height:180px" alt="product" />
					</div>
					<div class="row g-2">
						<div class="col-12"><strong>Name:</strong> <span id="viewProductName"></span></div>
						<div class="col-12"><strong>Category:</strong> <span id="viewProductCategory"></span></div>
						<div class="col-6"><strong>Quantity:</strong> <span id="viewProductQty"></span></div>
						<div class="col-6"><strong>Price:</strong> <span id="viewProductPrice"></span></div>
						<div class="col-6"><strong>Created:</strong> <span id="viewProductCreated"></span></div>
						<div class="col-6"><strong>Updated:</strong> <span id="viewProductUpdated"></span></div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Edit Product Modal -->
	<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<form id="editProductForm" enctype="multipart/form-data">
					<div class="modal-header">
						<h5 class="modal-title">Edit Product</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<input type="hidden" name="product_id" id="editProductId" />
						<div class="row g-3">
							<div class="col-12">
								<label class="form-label">Name</label>
								<input type="text" name="name" id="editProductName" class="form-control" required />
							</div>
							<div class="col-6">
								<label class="form-label">Quantity</label>
								<input type="number" name="quantity" id="editProductQty" class="form-control" min="0" value="0" required />
							</div>
							<div class="col-6">
								<label class="form-label">Price</label>
								<input type="number" step="0.01" name="price" id="editProductPrice" class="form-control" min="0" value="0" required />
							</div>
							<div class="col-12">
								<label class="form-label">Category</label>
								<select name="category" id="editProductCategory" class="form-select" required>
									<option value="consumables">Consumables</option>
									<option value="non_consumables">Non-consumables</option>
								</select>
							</div>
							<div class="col-12">
								<label class="form-label">Replace Picture (optional)</label>
								<input type="file" name="picture" id="editProductPicture" accept="image/*" class="form-control" />
								<div class="form-text">JPG, PNG, GIF, or WEBP. Max 5MB.</div>
								<img id="editProductPreview" class="img-fluid mt-2 rounded d-none" style="max-height:160px" alt="preview" />
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary">Save Changes</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- Archive Confirm Modal -->
	<div class="modal fade" id="archiveProductModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<form id="archiveProductForm">
					<div class="modal-header">
						<h5 class="modal-title">Archive Product</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<input type="hidden" name="product_id" id="archiveProductId" />
						<p class="mb-0">Are you sure you want to archive this product?</p>
						<small class="text-muted">You can keep your data while hiding it from the active lists.</small>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-warning">Archive</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- Delete Confirm Modal -->
	<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<form id="deleteProductForm">
					<div class="modal-header">
						<h5 class="modal-title">Delete Product</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<input type="hidden" name="product_id" id="deleteProductId" />
						<p class="mb-0">This will permanently remove the product. This action cannot be undone.</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-danger">Delete</button>
					</div>
				</form>
			</div>
		</div>
	</div>
