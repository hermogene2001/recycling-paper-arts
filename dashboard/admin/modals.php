<!-- Buttons to trigger modals -->
<div class="row justify-content-center mt-3">
        <div class="col-md-4 mb-3">
            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                <i class="fas fa-key me-2"></i>Change Password
            </button>
        </div>
        <!-- <div class="container mt-2">
    <div class="row"> -->
   
        <div class="col-md-4 mb-3">
            <button type="button" class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#createAgentModal">
            <i class="fas fa-plus me-2"></i>Create New Agent</button>
        </div>
        
    <!-- </div>
</div>
<div class="row justify-content-center mt-3"> -->
    <div class="col-md-4 mb-3">
        <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#addSocialLinksModal">
            <i class="fas fa-share-alt me-2"></i>Add Social Links
        </button>
    </div>
</div>

<!-- Create Agent Modal -->
<div class="modal fade" id="createAgentModal" tabindex="-1" aria-labelledby="createAgentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="create_agent.php">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createAgentModalLabel">Create New Agent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="name" name="fname" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="create_agent" class="btn btn-primary">Create Agent</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="change_password.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Social Links Modal -->
<div class="modal fade" id="addSocialLinksModal" tabindex="-1" aria-labelledby="addSocialLinksModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="add_social_links.php">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSocialLinksModalLabel">Add Social Links</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="whatsapp" class="form-label">WhatsApp</label>
                        <input type="url" class="form-control" id="whatsapp" name="whatsapp" placeholder="Enter WhatsApp link">
                    </div>
                    <div class="mb-3">
                        <label for="telegram" class="form-label">Telegram</label>
                        <input type="url" class="form-control" id="telegram" name="telegram" placeholder="Enter Telegram link">
                    </div>
                    <div class="mb-3">
                        <label for="facebook" class="form-label">Facebook</label>
                        <input type="url" class="form-control" id="facebook" name="facebook" placeholder="Enter Facebook link">
                    </div>
                    <div class="mb-3">
                        <label for="twitter" class="form-label">Twitter</label>
                        <input type="url" class="form-control" id="twitter" name="twitter" placeholder="Enter Twitter link">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_social_links" class="btn btn-primary">Save Links</button>
                </div>
            </div>
        </form>
    </div>
</div>
