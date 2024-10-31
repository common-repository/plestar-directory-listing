<div id="pdl-main-page" class="pdl-main-page pdl-main plestardirectory pdl-page <?php echo join(' ', $__page__['class']); ?>">

    <?php pdl_the_main_box(); ?>

    <?php echo $__page__['before_content']; ?>

    <div class="pdl-page-content <?php echo join(' ', $__page__['content_class']); ?>">
        <div id="pdl-categories" class="cf">
            <?php pdl_the_directory_categories(); ?>
        </div>

        <?php if ($listings) echo $listings; ?>
    </div>

</div>
