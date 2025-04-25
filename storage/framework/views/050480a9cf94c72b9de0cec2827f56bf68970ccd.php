
<?php $__env->startSection('title'); ?>
    <?php echo e(__('messages.rooms')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h4>rooms</h4>
                        <a href="<?php echo e(route('rooms.create')); ?>" class="btn btn-primary">Create New room</a>
                    </div>
                </div>

                <div class="card-body">

                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($room->id); ?></td>
                                    <td><?php echo e($room->name); ?></td>

                                    <td>
                                        <a href="<?php echo e(route('room.attandance',[$room->id,$room->slug()])); ?>" class="btn btn-sm btn-info">View</a>
                                        <a href="<?php echo e(route('rooms.edit', $room->id)); ?>" class="btn btn-sm btn-primary">Edit</a>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u167651649/domains/mutasemjaber.online/public_html/laila/resources/views/admin/rooms/index.blade.php ENDPATH**/ ?>