<div id="vehicles-section" class="content-section d-none">
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="mb-0 fw-bold">Vehicles</h6>
            <button class="btn btn-primary btn-sm" onclick="app.modals.openVehicleModal()">
                <i class="fa-solid fa-plus me-1"></i> Add Vehicles (Bulk)
            </button>
        </div>
        <div class="table-responsive">
            <table class="table align-items-center mb-0">
                <thead>
                    <tr>
                        <th>Vehicle Detail</th>
                        <th>Owner</th>
                        <th>License Plate</th>
                        <th>Color/Year</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="vehicles-table-body"></tbody>
            </table>
        </div>
    </div>
</div>