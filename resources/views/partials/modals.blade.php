<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg"> 
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="userForm" enctype="multipart/form-data">
                    <input type="hidden" id="userId">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" id="userName" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="userEmail" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" id="userPhone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" id="userPassword" placeholder="Enter password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Profile Image</label>
                                <input type="file" class="form-control" id="userProfileImage" accept="image/*">
                            </div>
                        </div>
                    </div> 
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="app.crud.saveUser()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Vehicle Type Modal -->
<div class="modal fade" id="typeModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vehicle Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="typeId">
                <div class="mb-3">
                    <label class="form-label">Category Name</label>
                    <input type="text" class="form-control" id="typeName" placeholder="e.g. Scooter">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100" onclick="app.crud.saveType()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Brand Modal -->
<div class="modal fade" id="brandModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Brands & Models (Bulk)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="brandForm">
                    <div id="brandsContainer" class="d-flex flex-column gap-4">
                        <!-- Dynamic brand inputs go here -->
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-success mt-2" onclick="app.crud.addBrandInput()">+ Add Brand</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="app.crud.saveBrands()">Save All Brands</button>
            </div>
        </div>
    </div>
</div>

<!-- Vehicle (Bulk) Modal -->
<div class="modal fade" id="vehicleModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Vehicles (Bulk)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Select Owner (User)</label>
                    <select class="form-select" id="bulkVehicleOwner"></select>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered" id="bulkVehicleTable">
                        <thead class="bg-light">
                            <tr>
                                <th>Type</th>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>Plate No.</th>
                                <th>Color</th>
                                <th>Year</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Rows added dynamically -->
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="app.crud.addVehicleRow()">
                    <i class="fa-solid fa-plus"></i> Add Another Vehicle
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="app.crud.saveBulkVehicles()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Plate Modal -->
<div class="modal fade" id="plateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Register Number Plate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Plate Number</label>
                    <input type="text" class="form-control text-uppercase" id="plateNumber" placeholder="KA 05 AB 1234">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="app.crud.savePlate()">Register</button>
            </div>
        </div>
    </div>
</div>

<!-- Ticket Detail Modal -->
<div class="modal fade" id="ticketModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ticket Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-xs fw-bold text-uppercase text-muted">Reporter</h6>
                        <div class="d-flex align-items-center mb-3">
                            <img src="" id="modalUserImg" class="avatar me-2">
                            <div>
                                <h6 class="mb-0 text-sm" id="modalUserName"></h6>
                                <p class="text-xs text-muted mb-0" id="modalUserPhone"></p>
                            </div>
                        </div>
                        <h6 class="text-xs fw-bold text-uppercase text-muted mt-4">Issue</h6>
                        <p class="fw-bold mb-1" id="modalCategory"></p>
                        <p class="text-sm text-muted" id="modalDesc"></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-xs fw-bold text-uppercase text-muted">Location</h6>
                        <div id="map"></div>
                        <div class="mt-3">
                            <input type="hidden" id="modalTicketId">
                            <label class="form-label text-xs fw-bold">Update Status</label>
                            <select class="form-select form-select-sm" id="modalStatusSelect">
                                <option value="Pending">Pending</option>
                                <option value="Assigned">Assigned</option>
                                <option value="Resolved">Resolved</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="app.crud.updateTicket()">Update Ticket</button>
            </div>
        </div>
    </div>
</div>