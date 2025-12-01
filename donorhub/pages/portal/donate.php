<?php
$is_logged_in = isset($_SESSION['user_id']); 
?>

<div class="donate" data-parallax="scroll" data-image-src="assets/img/donate.jpg">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="donate-content">
                    <div class="section-header">
                        <p style="color: #06cfd3ff!important;font-weight:bold;font-size:20pt;">Donate Now</p>
                        <h2>Let's donate to needy people for better lives</h2>
                    </div>
                    <div class="donate-text">
                        <p>
                            Lorem ipsum dolor sit amet elit. Phasellus nec pretium mi. Curabitur facilisis ornare velit non. Aliquam metus tortor, auctor id gravida, viverra quis sem. Curabitur non nisl nec nisi maximus. Aenean convallis porttitor. Aliquam interdum at lacus non blandit.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="donate-form">
                    <form action="" method="POST">
                        <div class="control-group">
                            <input type="text" class="form-control" placeholder="Name" required="required" />
                        </div>
                        <div class="control-group">
                            <input type="email" class="form-control" placeholder="Email" required="required" />
                        </div>
                        <div class="btn-group btn-outline-dark" role="group" aria-label="Donate amount">
                            <input type="radio" class="btn-check" name="options" id="option1" autocomplete="off" checked>
                            <label class="btn btn-custom" for="option1">$10</label>

                            <input type="radio" class="btn-check" name="options" id="option2" autocomplete="off">
                            <label class="btn btn-custom " for="option2">$20</label>

                            <input type="radio" class="btn-check" name="options" id="option3" autocomplete="off">
                            <label class="btn btn-custom" for="option3">$30</label>
                        </div>

                        <div>
                            <?php if ($is_logged_in): ?>
                                <!-- Redirect to donation page if logged in -->
                                <button class="btn btn-custom" type="submit" formaction="home.php?page=17">Donate Now</button>
                            <?php else: ?>
                                <!-- Redirect to login page if not logged in -->
                                <button class="btn btn-custom btn-outline-dark" type="submit" formaction="login.php">Log in/Register to Donate</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
