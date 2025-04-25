

<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2><?php echo e($multipleUsers ? 'All Users Attendance Logs' : 'Attendance Logs for ' . $user->name); ?></h2>
                </div>

                <div class="card-body">
                    <?php if(session('status')): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo e(session('status')); ?>

                        </div>
                    <?php endif; ?>

                    <?php $__currentLoopData = $userData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($multipleUsers): ?>
                            <h3 class="mb-3"><?php echo e($data['user']->name); ?></h3>
                        <?php endif; ?>

                        <!-- Room Summary Cards -->
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                <h4>Room Time Summary</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-md-3 mb-3">
                                            <div class="card <?php echo e($room->is_main ? 'border-primary' : ''); ?>">
                                                <div class="card-header <?php echo e($room->is_main ? 'bg-primary text-white' : 'bg-light'); ?>">
                                                    <?php echo e($room->name); ?>

                                                </div>
                                                <div class="card-body">
                                                    <h5 class="card-title">
                                                        <?php
                                                        $isCurrentlyInRoom = false;
                                                        foreach($data['logs'] as $log) {
                                                            if ($log->room_id == $room->id && $log->type == 'in' && 
                                                                !$data['logs']->where('room_id', $room->id)->where('type', 'out')
                                                                ->where('time', '>', $log->time)->count()) {
                                                                $isCurrentlyInRoom = true;
                                                            }
                                                        }
                                                    ?>
                                                    
                                                    <?php if(isset($data['formattedTotalTime'][$room->id])): ?>
                                                        <span class="badge bg-success"><?php echo e($data['formattedTotalTime'][$room->id]); ?></span>
                                                    <?php elseif($isCurrentlyInRoom): ?>
                                                        <span class="badge bg-primary">Currently In</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">No visits</span>
                                                    <?php endif; ?>
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Daily Logs Table -->
                        <div class="card mb-4">
                            <div class="card-header bg-dark text-white">
                                <h4>Detailed Attendance by Day</h4>
                            </div>
                            <div class="card-body p-0">
                                <?php $__empty_1 = true; $__currentLoopData = $data['logsByDay']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayNo => $logs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="day-section mb-4">
                                        <div class="day-header bg-light p-2 border">
                                            <h5 class="mb-0">Day <?php echo e($dayNo); ?></h5>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered mb-0">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th class="text-center">Time</th>
                                                        <th class="text-center">Room</th>
                                                        <th class="text-center">Action</th>
                                                        <th class="text-center">Duration</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        $roomEntryTimes = [];
                                                        $rowClass = '';
                                                    ?>
                                                    
                                                    <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $roomName = $rooms->where('id', $log->room_id)->first()->name;
                                                            $isHistory = $log instanceof App\Models\AttendanceLogHistory;
                                                            $time = \Carbon\Carbon::parse($log->time);
                                                            $duration = null;
                                                            
                                                            // Track entry time for calculating duration
                                                            if ($log->type === 'in') {
                                                                $roomEntryTimes[$log->room_id] = $time;
                                                                $rowClass = 'table-success';
                                                            } else if (isset($roomEntryTimes[$log->room_id])) {
                                                                $entryTime = $roomEntryTimes[$log->room_id];
                                                                $duration = $time->diff($entryTime);
                                                                unset($roomEntryTimes[$log->room_id]);
                                                                $rowClass = 'table-danger';
                                                            }
                                                            
                                                            // Check if this is the last entry (user still in room)
                                                            $isLastEntry = $log->type === 'in' && 
                                                                !$logs->where('room_id', $log->room_id)
                                                                     ->where('type', 'out')
                                                                     ->where('time', '>', $log->time)
                                                                     ->count();
                                                        ?>
                                                        
                                                        <tr class="<?php echo e($rowClass); ?> <?php echo e($isLastEntry ? 'fw-bold' : ''); ?>">
                                                            <td class="text-center"><?php echo e($time->format('H:i:s')); ?></td>
                                                            <td class="text-center">
                                                                <span class="badge bg-<?php echo e($rooms->where('id', $log->room_id)->first()->is_main ? 'primary' : 'info'); ?> fs-6">
                                                                    <?php echo e($roomName); ?>

                                                                </span>
                                                            </td>
                                                          
                                                            <td class="text-center">
                                                                <span class="badge bg-<?php echo e($isHistory ? 'warning' : 'info'); ?> fs-6">
                                                                    <?php echo e($isHistory ? 'Historical' : 'Current'); ?>

                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <?php if($duration && $log->type === 'out'): ?>
                                                                    <span class="badge bg-dark fs-6">
                                                                    <?php echo e(sprintf('%02d:%02d:%02d', 
                                                                        $duration->h + ($duration->days * 24), 
                                                                        $duration->i, 
                                                                        $duration->s)); ?>

                                                                    </span>
                                                                <?php else: ?>
                                                                    -
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="alert alert-warning m-3">No attendance logs found for this user.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Full Timeline View -->
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h4>Complete Attendance Timeline</h4>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <?php
                                        $prevDay = null;
                                        $roomStatus = [];
                                    ?>
                                    
                                    <?php $__currentLoopData = $data['logs']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $time = \Carbon\Carbon::parse($log->time);
                                            $roomName = $rooms->where('id', $log->room_id)->first()->name;
                                            $isHistory = $log instanceof App\Models\AttendanceLogHistory;
                                            
                                            // Add day separator if needed
                                            if($prevDay !== $log->day_no) {
                                                echo '<div class="timeline-item day-marker">
                                                    <div class="timeline-marker bg-dark"></div>
                                                    <div class="timeline-content">
                                                        <h4 class="mb-0">Day ' . $log->day_no . '</h4>
                                                    </div>
                                                </div>';
                                                $prevDay = $log->day_no;
                                            }
                                        ?>
                                        
                                        <div class="timeline-item">
                                            <div class="timeline-marker <?php echo e($log->type === 'in' ? 'bg-success' : 'bg-danger'); ?>"></div>
                                            <div class="timeline-content">
                                                <p class="timeline-date mb-0"><?php echo e($time->format('H:i:s')); ?></p>
                                                <h5><?php echo e(ucfirst($log->type)); ?> <?php echo e($roomName); ?></h5>
                                                <p class="timeline-text">
                                                    <span class="badge bg-<?php echo e($isHistory ? 'warning' : 'info'); ?>">
                                                        <?php echo e($isHistory ? 'Historical Record' : 'Current Record'); ?>

                                                    </span>
                                                    
                                                    <?php if($log->type === 'in'): ?>
                                                        <?php $roomStatus[$log->room_id] = $time; ?>
                                                    <?php elseif(isset($roomStatus[$log->room_id])): ?>
                                                        <?php
                                                            $entryTime = $roomStatus[$log->room_id];
                                                            $duration = $time->diff($entryTime);
                                                            $durationStr = sprintf('%02d:%02d:%02d', 
                                                                $duration->h + ($duration->days * 24), 
                                                                $duration->i, 
                                                                $duration->s);
                                                        ?>
                                                        <span class="badge bg-info ms-2">
                                                            Duration: <?php echo e($durationStr); ?>

                                                        </span>
                                                        <?php unset($roomStatus[$log->room_id]); ?>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if($multipleUsers): ?>
                            <hr class="my-5">
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Timeline styling */
    .timeline {
        position: relative;
        padding: 20px 0;
        list-style: none;
        margin: 0;
    }

    .timeline:before {
        content: " ";
        position: absolute;
        top: 0;
        bottom: 0;
        left: 20px;
        width: 3px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
        padding-left: 40px;
    }

    .timeline-marker {
        position: absolute;
        top: 0;
        left: 10px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 0 0 3px #e9ecef;
        z-index: 1;
    }
    
    /* Pulsing effect for current location */
    .timeline-marker.pulse {
        animation: pulse-animation 2s infinite;
        box-shadow: 0 0 0 5px rgba(25, 135, 84, 0.4);
    }
    
    @keyframes pulse-animation {
        0% {
            box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7);
        }
        70% {
            box-shadow: 0 0 0 15px rgba(25, 135, 84, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(25, 135, 84, 0);
        }
    }

    .timeline-content {
        padding: 15px;
        border-radius: 6px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        transition
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\laila\resources\views/admin/users/showLog.blade.php ENDPATH**/ ?>