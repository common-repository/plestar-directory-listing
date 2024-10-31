<ul id="pdl-listing-metabox-tab-selector" class="pdl-admin-tab-nav subsubsub">
    <?php foreach ( $tabs as $tab ): ?>
    <li><a href="#pdl-listing-metabox-<?php echo $tab['id']; ?>"><?php echo $tab['label']; ?></a></li>
    <?php endforeach; ?>
</ul>

<?php foreach ( $tabs as $tab ): ?>
    <?php echo $tab['content']; ?>
<?php endforeach; ?>
