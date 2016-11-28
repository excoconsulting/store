<div class="edgtf-tabs-content">
    <div class="tab-content">

        <div class="tab-pane fade in active">
            <div class="edgtf-tab-content">
                <h2 class="edgtf-page-title"><?php echo esc_html($page->title); ?></h2>


                <form method="post" class="edgt_ajax_form">
                    <div class="edgtf-page-form">

                        <?php $page->render(); ?>
                    </div>
                </form>

            </div><!-- close edgtf-tab-content -->
        </div>

    </div>
</div> <!-- close div.edgtf-tabs-content -->