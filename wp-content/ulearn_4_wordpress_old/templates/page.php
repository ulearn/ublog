<div id="art-main">
    <div class="art-sheet">
        <div class="art-sheet-tl"></div>
        <div class="art-sheet-tr"></div>
        <div class="art-sheet-bl"></div>
        <div class="art-sheet-br"></div>
        <div class="art-sheet-tc"></div>
        <div class="art-sheet-bc"></div>
        <div class="art-sheet-cl"></div>
        <div class="art-sheet-cr"></div>
        <div class="art-sheet-cc"></div>
        <div class="art-sheet-body">
            <div class="art-header">
                <div class="art-header-jpeg"></div>
                <div class="art-logo">
                <h1 id="name-text" class="art-logo-name">
                        <a href="<?php echo $logo_url; ?>/"><?php echo $logo_name; ?></a></h1>
                    <div id="slogan-text" class="art-logo-text"><?php echo $logo_description; ?></div>
                </div>
            </div>
            <div class="art-nav">
            	<div class="l"></div>
            	<div class="r"></div>
            	<ul class="art-menu">
            		<?php echo $menu_items; ?>
            	</ul>
            </div>
            <div class="art-content-layout">
                <div class="art-content-layout-row">
                    <div class="art-layout-cell art-content">
                        <?php echo $sidebarTop; ?>
                            <?php echo $content; ?>    
                        <?php echo $sidebarBottom; ?>    
                    </div>
                    <div class="art-layout-cell art-sidebar1">
                        <?php echo $sidebar1; ?>    
                    </div>
                </div>
            </div>
            <div class="cleared"></div><div class="art-footer">
                <div class="art-footer-t"></div>
                <div class="art-footer-l"></div>
                <div class="art-footer-b"></div>
                <div class="art-footer-r"></div>
                <div class="art-footer-body">
                  <?php echo $sidebarFooter; ?>
                  <?php echo $footerRSS; ?>
                  <div class="art-footer-text">
                      <?php echo $footerText; ?>
                      
                  </div>
            		<div class="cleared"></div>
                </div>
            </div>
    		<div class="cleared"></div>
        </div>
    </div>
    <div class="cleared"></div>
    <p class="art-page-footer"></p>
</div>
