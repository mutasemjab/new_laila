<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
        <img src="<?php echo e(asset('assets/admin/dist/img/AdminLTELogo.png')); ?>" alt="AdminLTE Logo"
            class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Alien-code</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="<?php echo e(asset('assets/admin/dist/img/user2-160x160.jpg')); ?>" class="img-circle elevation-2"
                    alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block"><?php echo e(auth()->user()->name); ?></a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
                     with font-awesome or any other icon font library -->


                
                <li class="nav-item">
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link">
                        <i class="fa fa-home nav-icon"></i>
                        <p><?php echo e(__('messages.Home')); ?></p>
                    </a>
                </li>
                

                
                <?php if($user->can('day-table')): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('day.index')); ?>" class="nav-link">
                        <i class="far fa fa-calendar nav-icon"></i>
                        <p><?php echo e(__('messages.Days statics')); ?></p>
                    </a>
                </li>
                <?php endif; ?>
                

                
                <?php if($user->can('day-table')): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('day.qualified')); ?>" class="nav-link">
                        <i class="fa fa-certificate nav-icon"></i>
                        <p><?php echo e(__('messages.Qualified users')); ?></p>
                    </a>
                </li>
                <?php endif; ?>
                


                <?php if(
                $user->can('user-table') ||
                $user->can('user-add') ||
                $user->can('user-edit') ||
                $user->can('user-delete')): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('users.index')); ?>" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p> <?php echo e(__('messages.users')); ?> </p>
                    </a>
                </li>
                <?php endif; ?>






                



                <li class="nav-item">
                    <a href="<?php echo e(route('admin.login.edit',auth()->user()->id)); ?>" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p><?php echo e(__('messages.Admin_account')); ?> </p>
                    </a>
                </li>

                <?php if($user->can('role-table') || $user->can('role-add') || $user->can('role-edit') ||
                $user->can('role-delete')): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('admin.role.index')); ?>" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <span><?php echo e(__('messages.Roles')); ?> </span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if(
                $user->can('employee-table') ||
                $user->can('employee-add') ||
                $user->can('employee-edit') ||
                $user->can('employee-delete')): ?>
                <li class="nav-item">
                    <a href="<?php echo e(route('admin.employee.index')); ?>" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <span> <?php echo e(__('messages.Employee')); ?> </span>
                    </a>
                </li>
                <?php endif; ?>


                <?php $rooms = 0 ; $rooms = \App\Models\Room::all(); ?>

                <?php if( $rooms->count() &&
                ($user->can('room-table') ||
                $user->can('room-add') ||
                $user->can('room-edit') ||
                $user->can('room-delete')) ): ?>

                    <li class="nav-item">
                        <a href="<?php echo e(route('rooms.index')); ?>" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p> <?php echo e(__('messages.rooms')); ?> </p>
                        </a>
                    </li>
                    <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('room.attandance',[$room->id,$room->slug()])); ?>" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p> <?php echo e($room->name); ?> </p>
                        </a>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php endif; ?>

                <?php if(
                    $user->can('print-table') ||
                    $user->can('print-add') ||
                    $user->can('print-edit') ||
                    $user->can('print-delete')): ?>
                    <li class="nav-item">
                        <a href="<?php echo e(route('admin.print.badge')); ?>" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <span> <?php echo e(__('messages.Print Badges')); ?> </span>
                        </a>
                    </li>
                <?php endif; ?>


            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
<?php /**PATH C:\xampp\htdocs\laila\resources\views/admin/includes/sidebar.blade.php ENDPATH**/ ?>