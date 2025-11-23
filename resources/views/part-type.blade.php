<div class="modal fade" id="typeModal" tabindex="-1" role="dialog" aria-labelledby="typeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="typeModalLabel">Add Vehicle Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="typeForm">
                    <div class="mb-3">
                        <label for="typeName" class="form-label">Type Name</label>
                        <input type="text" class="form-control" id="typeName" required placeholder="e.g., Car, Bike, Truck">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" onclick="app.crud.saveType()">Save Type</button>
            </div>
        </div>
    </div>
</div>