<?php
// Fix: This must be the very first thing in your file, with no leading spaces or blank lines.
session_start();
ob_start();

require_once("include/admin/header.php");
?>
<!-- /.header -->

<!-- Navbar -->
<?php
require_once("include/admin/nav.php");
?>
<!-- /.navbar -->

<!-- Main Sidebar Container -->
<?php
require_once("include/admin/sidebar.php");
?>
<!-- Main content -->
<section class="content p-5 m-5">

    <!-- Default box -->
    <div class="card">
        <?php
        // Note: You should remove the session_start() and ob_start() calls from placeholder.php now.
        include("placeholder.php");
        ?>
    </div>
    <!-- /.card -->

</section>
</div>

<!-- Main Footer -->
<?php
require_once("include/admin/footer.php");
?>