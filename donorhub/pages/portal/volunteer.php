<?php
$is_logged_in = isset($_SESSION['user_id']); 
?>

<div class="volunteer" data-parallax="scroll" data-image-src="assets/img/img/volunteer.jpg">
    <div class="container">
        <div class="row align-items-center p-5 ">
            <div class="col-lg-5 vform">
                <div class="volunteer-form rounded">
                    <form action="" method="POST" class="rounded">
                        <div class="control-group btn-outline-dark">
                            <input type="text" class="form-control rounded" placeholder="Name" name="name" required />
                        </div>
                        <div class="control-group btn-outline-dark">
                            <input type="email" class="form-control rounded" placeholder="Email" name="email" required />
                        </div>
                        <div class="control-group btn-outline-dark">
                            <textarea class="form-control rounded" placeholder="Why do you want to become a volunteer?" name="reason" rows="4" required></textarea>
                        </div>

                        <div>
                            <?php if ($is_logged_in): ?>
                                <!-- Show actual submit if logged in -->
                                <button class="btn btn-custom btn-outline-light" type="submit" formaction="volunteer_submit.php">Become a Volunteer</button>
                            <?php else: ?>
                                <!-- Prompt login if not logged in -->
                                <button class="btn btn-custom btn-outline-light" type="submit" formaction="login.php">Log in/Register to be a Volunteer</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="volunteer-content">
                    <div class="section-header">
                        <p style="color: #06cfd3ff!important;font-weight:bold;font-size:20pt;">Become A Volunteer</p>
                        <h2>Let’s make a difference in the lives of others</h2>
                    </div>
                    <div class="volunteer-text">
                        <p>
                            Lorem ipsum dolor sit amet elit. Phasellus nec pretium mi. Curabitur facilisis ornare velit non. Aliquam metus tortor, auctor id gravida, viverra quis sem. Curabitur non nisl nec nisi maximus. Aenean convallis porttitor. Aliquam interdum at lacus non blandit.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
