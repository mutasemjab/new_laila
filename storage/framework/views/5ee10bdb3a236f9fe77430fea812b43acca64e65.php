
<?php if(isset($users) && $users->count()): ?>
<?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
        <td><?php echo e($user->id); ?></td>
        <td><?php echo e($user->name); ?></td>
        <td><?php echo $user->categoryLabel(0); ?></td>
        <!-- <td><?php echo e($user->email); ?></td> -->
        <td><?php echo e($user->barcode); ?></td>
        <td><?php echo $user->calculateAveragePresenceHistory()['avg']; ?></td>
        <td><?php echo $user->calculateAveragePresenceHistory()['sum']; ?></td>
        <td>
            <a href="<?php echo e(route('users.show', $user->id)); ?>" class="btn btn-sm btn-info">View</a>
            <a href="<?php echo e(route('users.edit', $user->id)); ?>" class="btn btn-sm btn-primary">Edit</a>
            <a href="<?php echo e(route('user-time.show', $user->id)); ?>" class="btn btn-sm btn-secondary">Attendance</a>

        </td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php else: ?>
  <tr> No user found for this search</tr>
<?php endif; ?>
<?php /**PATH /home/u167651649/domains/mutasemjaber.online/public_html/laila/resources/views/admin/days/attandance-users.blade.php ENDPATH**/ ?>