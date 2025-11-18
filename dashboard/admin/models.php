<div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" name="price" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Profit Rate (%)</label>
                            <input type="number" name="profit_rate" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Days (Cycle)</label>
                            <input type="number" name="cycle" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Compound Product Modal -->
    <div class="modal fade" id="createCompoundProductModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Compound Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" name="price" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Profit Rate (%)</label>
                            <input type="number" name="profit_rate" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Days (Cycle)</label>
                            <input type="number" name="cycle" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="create_product" class="btn btn-warning">Create Compound Product</button>
                    </div>
                </div>
            </form>
        </div>
    </div>