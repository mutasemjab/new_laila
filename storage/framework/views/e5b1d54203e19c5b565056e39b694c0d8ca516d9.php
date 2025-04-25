<!DOCTYPE html>
<html>
<head>
    <title>Print Badges</title>
    <style>
        @media print {
            .page-break {
                page-break-after: always;
            }
            .print-btn {
                display: none;
            }
            @page {
                size: A4;
                margin: 0;
            }
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .a4-page {
            width: 210mm;
            height: 297mm;
            padding: 10mm;
            box-sizing: border-box;
            page-break-after: always;
        }

        .badge-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 10mm;
            height: 100%;
        }

        .visitor-badge {
            border: 1px solid #ccc;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            background-color: white;
            height: 100%;
            display: flex;
            flex-direction: column;
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
            max-height: 40px;
        }

        .badge-category {
            text-align: center;
            padding: 6px;
            font-weight: bold;
            color: white;
            font-size: 14px;
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
            padding: 15px;
            text-align: center;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .attendee-name h3 {
            margin: 0 0 10px 0;
            font-size: 20px;
            color: #333;
            font-weight: bold;
        }

        .attendee-company h4 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: #555;
        }

        .attendee-country h5 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #777;
        }

        .position-row {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 5px 0;
        }

        .position-label {
            font-weight: bold;
            margin-right: 10px;
            font-size: 14px;
        }

        .badge-barcode {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
        }

        .barcode-text {
            margin-top: 5px;
            font-size: 12px;
            letter-spacing: 1px;
        }

        .print-btn {
            margin: 15px;
            text-align: center;
        }

        .print-btn button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .print-btn button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <?php
        $users = App\Models\User::get();
    ?>
    
    <div class="print-btn">
        <button onclick="window.print()">Print Badges</button>
    </div>

    <?php $__currentLoopData = $users->chunk(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chunk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="a4-page">
            <div class="badge-container">
                <?php $__currentLoopData = $chunk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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

                        <div class="badge-body">
                            <div>
                                <div class="attendee-name">
                                    <h3><?php echo e($user->name); ?></h3>
                                </div>
                                <div class="attendee-country">
                                    <h5><?php echo e($user->country); ?></h5>
                                </div>
                                <div class="attendee-country">
                                    <h5 style="text-align: left;">Position: </h5>
                                </div>
                                <?php echo $user->categoryLabel(false); ?>

                            </div>
                            
                            <div>
                                <?php if($user->category != 3 && $user->category != 5 && $user->category != 6): ?>
                                <div class="badge-barcode">
                                    <svg id="barcode-<?php echo e($user->id); ?>"></svg>
                                    <p class="barcode-text"><?php echo e($user->barcode); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($user->category != 3 && $user->category != 5 && $user->category != 6): ?>
                    JsBarcode("#barcode-<?php echo e($user->id); ?>", "<?php echo e($user->barcode); ?>", {
                        format: "CODE128",
                        lineColor: "#000",
                        width: 2,
                        height: 60,
                        displayValue: false
                    });
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        });
    </script>
</body>
</html><?php /**PATH /home/u167651649/domains/mutasemjaber.online/public_html/laila/resources/views/print_badge.blade.php ENDPATH**/ ?>