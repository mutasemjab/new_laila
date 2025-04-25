
<?php $__env->startSection('title'); ?>
    <?php echo e(__('messages.users')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Import Users from Excel</h4>
                </div>
                <div class="card-body">
                    <?php if(session('import_success')): ?>
                        <div class="alert alert-success">
                            <?php echo e(session('import_success')); ?>

                        </div>
                    <?php endif; ?>

                    <?php if(session('import_error')): ?>
                        <div class="alert alert-danger">
                            <?php echo e(session('import_error')); ?>

                        </div>
                    <?php endif; ?>

                    <form action="<?php echo e(route('users.import')); ?>" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                        <?php echo csrf_field(); ?>
                        <div class="col-md-8">
                            <label for="excel_file" class="form-label">Excel File</label>
                            <input type="file" class="form-control" id="excel_file" name="file" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">Upload .xlsx, .xls, or .csv files with user data</div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-file-import me-1"></i> Import Users
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h4>Users</h4>
                        <a href="<?php echo e(route('users.create')); ?>" class="btn btn-primary">Create New User</a>
                    </div>
                </div>

                <div class="card-body">
                    <?php if(isset($users) && $users->count()): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Email</th>
                                <th>Barcode</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($user->id); ?></td>
                                    <td><?php echo e($user->name); ?></td>
                                    <td><?php echo $user->categoryLabel(); ?></td>
                                    <td><?php echo e($user->email); ?></td>
                                    <td><?php echo e($user->barcode); ?></td>
                                    <td>
                                        <a href="<?php echo e(route('users.show', $user->id)); ?>" class="btn btn-sm btn-info">View</a>
                                        <a href="<?php echo e(route('users.edit', $user->id)); ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="<?php echo e(route('user-time.show', $user->id)); ?>" class="btn btn-sm btn-secondary">Attendance</a>
                                    
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                    <?php echo e($users->links()); ?>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u167651649/domains/mutasemjaber.online/public_html/laila/resources/views/admin/users/index.blade.php ENDPATH**/ ?>