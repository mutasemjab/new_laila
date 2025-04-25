

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Cumulative Time in Conference Rooms (Excluding Main Room)</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Attendee</th>
                                    <th>Barcode</th>
                                    <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <th>Day <?php echo e($day->id); ?></th>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <th>Cumulative Total</th>
                                    <th>Room Breakdown</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $userData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $userId => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($data['user']->name); ?></td>
                                        <td><?php echo e($data['user']->barcode); ?></td>
                                        
                                        <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <td>
                                                <?php echo e($data['daily_breakdown'][$day->id] ?? '0 hours, 0 minutes'); ?>

                                            </td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        
                                        <td><strong><?php echo e($data['total_time']); ?></strong></td>
                                        
                                        <td>
                                            <button class="btn btn-sm btn-info" type="button" data-toggle="collapse" 
                                                    data-target="#roomBreakdown<?php echo e($userId); ?>" aria-expanded="false">
                                                View Details
                                            </button>
                                            
                                            <div class="collapse mt-2" id="roomBreakdown<?php echo e($userId); ?>">
                                                <ul class="list-group">
                                                    <?php $__currentLoopData = $data['room_breakdown']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $roomId => $roomData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                            <?php echo e($roomData['name']); ?>

                                                            <span class="badge badge-primary badge-pill"><?php echo e($roomData['time']); ?></span>
                                                        </li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="<?php echo e(count($days) + 4); ?>" class="text-center">No attendance data found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
<script>
    $(document).ready(function() {
        // You can add any additional JavaScript functionality here
        // For example, you might want to add sorting or filtering capabilities
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u167651649/domains/mutasemjaber.online/public_html/laila/resources/views/admin/days/days.blade.php ENDPATH**/ ?>