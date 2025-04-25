
<?php $__env->startSection('title'); ?>
    <?php echo e(__('messages.Show')); ?> <?php echo e(__('messages.Customers')); ?>

<?php $__env->stopSection(); ?>



<?php $__env->startSection('css'); ?>
<style>
    /* Normal view styling */
    .visitor-badge-container {
        max-width: 380px;
        margin: 0 auto;
    }

    .visitor-badge {
        border: 1px solid #ccc;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        background-color: white;
    }

    .badge-header {
        padding: 15px;
        background-color: #f8f9fa;
        border-bottom: 1px solid #eee;
    }

    .logos-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .logo img {
        max-height: 50px;
    }

    .badge-category {
        text-align: center;
        padding: 8px;
        font-weight: bold;
        color: white;
        font-size: 16px;
        text-transform: uppercase;
    }

    .badge-category-speaker {
        background-color: #dc3545;
    }

    .badge-category-participant {
        background-color: #007bff;
    }

    .badge-category-exhibitor {
        background-color: #28a745;
    }

    .badge-category-committee {
        background-color: #6c757d;
    }

    .badge-body {
        padding: 20px;
    }

    .attendee-name h3 {
        margin: 0 0 10px 0;
        font-size: 22px;
        color: #333;
        font-weight: bold;
    }

    .attendee-company h4 {
        margin: 0 0 5px 0;
        font-size: 18px;
        color: #555;
    }

    .attendee-country h5 {
        margin: 0 0 15px 0;
        font-size: 16px;
        color: #777;
    }

    .attendee-contact {
        font-size: 14px;
        color: #666;
        margin-bottom: 15px;
    }

    .badge-barcode {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px dashed #ccc;
    }

    .simple-barcode {
        display: flex;
        justify-content: center;
        align-items: flex-end;
        height: 70px;
        margin: 10px 0;
    }

    .barcode-line {
        width: 2px;
        background-color: #000;
        margin: 0 1px;
    }

    .barcode-text {
        margin-top: 5px;
        font-size: 14px;
        letter-spacing: 1px;
    }

    /* Print-specific styles */
    @media print {
        body * {
            visibility: hidden;
        }

        .visitor-badge-container, .visitor-badge-container * {
            visibility: visible;
        }

        .visitor-badge-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .visitor-badge {
            width: 3.5in; /* Standard badge width */
            height: 5in; /* Standard badge height */
            box-shadow: none;
            border: 1px solid #000;
        }

        .badge-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: calc(100% - 120px); /* Adjust based on header height */
        }
    }
</style>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h4>User Details</h4>
                        <a href="<?php echo e(route('users.index')); ?>" class="btn btn-secondary">Back to Users</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>User Information</h5>
                            <p><strong>ID:</strong> <?php echo e($user->id); ?></p>
                            <p><strong>Title:</strong> <?php echo e($user->title); ?></p>
                            <p><strong>Name:</strong> <?php echo e($user->name); ?> </p>
                            <p><strong>Company:</strong> <?php echo e($user->company); ?></p>
                            <p><strong>Country:</strong> <?php echo e($user->country); ?></p>
                            <p><strong>Phone:</strong> <?php echo e($user->phone); ?></p>
                            <p><strong>Email:</strong> <?php echo e($user->email); ?></p>
                            <p><strong>Category: <?php echo $user->categoryLabel(false); ?></strong></p>
                            <p><strong>Created At:</strong> <?php echo e($user->created_at->format('Y-m-d H:i:s')); ?></p>

                            <div class="mt-3">
                                
                                <a href="<?php echo e(route('users.edit', $user->id)); ?>" class="btn btn-primary">Edit User</a>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5 class="text-center">Preview Visitor Badge</h5>
                            <div class="mt-3">
                                <button class="btn btn-success w-100 mb-3" onclick="window.print()">Print Visitor Badge</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visitor Badge Design (Hidden in normal view, visible when printing) -->
<div class="row mt-4 justify-content-center">
    <div class="col-md-8">
        <div class="visitor-badge-container">
            <div class="visitor-badge">
                <div class="badge-header">
                    <div class="logos-container">
                        <div class="logo">
                            <img src="<?php echo e(asset('assets/admin/imgs/logo2.jpeg')); ?>" alt="Logo 2" onerror="this.style.display='none'">
                        </div>
                        <div class="logo">
                            <img src="<?php echo e(asset('assets/admin/imgs/logo1.jpeg')); ?>" alt="Logo 1" onerror="this.style.display='none'">
                        </div>
                        <div class="logo">
                            <img src="<?php echo e(asset('assets/admin/imgs/logo3.jpeg')); ?>" alt="Logo 3" onerror="this.style.display='none'">
                        </div>
                    </div>
                </div>


                <div class="badge-body text-center">
                    <div class="attendee-name">
                        <h3><?php echo e($user->name); ?></h3>
                    </div>
                    <div class="attendee-country">
                        <h5><?php echo e($user->country); ?></h5>
                    </div>
                    <div class="attendee-country">
                        <h5 style="padding-right: 130px;">Position : </h5>
                    </div>
                    <?php echo $user->categoryLabel(false); ?>

                    <?php if($user->category == 3 || $user->category == 5 || $user->category == 6): ?>
                    <?php else: ?>
                    <div class="badge-barcode text-center">
                        <svg id="barcode"></svg>
                        <p class="barcode-text"><?php echo e($user->barcode); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>


<?php $__env->stopSection(); ?>


<?php $__env->startSection('script'); ?>
<!-- Add this before your closing </body> tag -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        JsBarcode("#barcode", "<?php echo e($user->barcode); ?>", {
            format: "CODE128",
            lineColor: "#000",
            width: 2,
            height: 60,
            displayValue: false
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u167651649/domains/mutasemjaber.online/public_html/laila/resources/views/admin/users/show.blade.php ENDPATH**/ ?>