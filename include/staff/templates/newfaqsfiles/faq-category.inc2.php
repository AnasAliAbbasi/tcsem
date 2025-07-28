


<?php
if(!defined('OSTSTAFFINC') || !$category || !$thisstaff) die('Access Denied');

?>
<div class="has_bottom_border" style="margin-bottom:5px; padding-top:5px;">
<div class="pull-left">
  <h2><?php echo __('Frequently Asked Questions');?></h2>
</div>
<?php if ($thisstaff->hasPerm(FAQ::PERM_MANAGE)) {
echo sprintf('<div class="pull-right flush-right">
    <a class="green action-button" href="faq.php?cid=%d&a=add">'.__('Add New FAQ').'</a>
    <span class="action-button" data-dropdown="#action-dropdown-more"
          style="/*DELME*/ vertical-align:top; margin-bottom:0">
        <i class="icon-caret-down pull-right"></i>
        <span ><i class="icon-cog"></i>'. __('More').'</span>
    </span>
    <div id="action-dropdown-more" class="action-dropdown anchor-right">
        <ul>
            <li><a class="user-action" href="categories.php?id=%d">
                <i class="icon-pencil icon-fixed-width"></i>'
                .__('Edit Category').'</a>
            </li>
            <li class="danger">
                <a class="user-action" href="categories.php">
                    <i class="icon-trash icon-fixed-width"></i>'
                    .__('Delete Category').'</a>
            </li>
        </ul>
    </div>
</div>', $category->getId(), $category->getId());
} else {
?><?php
} ?>
    <div class="clear"></div>

</div>
<div class="faq-category">
    <div style="margin-bottom:10px;">
        <div class="faq-title pull-left"><?php echo $category->getFullName() ?></div>
        <div class="faq-status inline">(<?php echo $category->isPublic()?__('Public'):__('Internal'); ?>)</div>
        <div class="clear"><time class="faq"> <?php echo __('Last Updated').' '. Format::daydatetime($category->getUpdateDate()); ?></time></div>
    
    
    </div>
    <?php

    $current_page = basename(__FILE__);
    $back_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '#'; // Default to '#' if no referer is available

    echo '
    <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="kb2.php">Home</a></li>
        <li class="breadcrumb-item"> Faqs Categories</li>
    </ol>
    </nav>';
    ?>
    <div class="cat-desc has_bottom_border">
    <?php echo Format::display($category->getDescription());
    if ($category->children) {
        echo '<p/><div class="row">';
           
        foreach ($category->children as $c) {
            echo '<div class="col-md-3">';
                echo '<div class="card" style="height:270px !important;">';
                    echo '<div class="text-center icon">
                        <i class="fas fa-folder-open"></i>
                    </div>';
                    echo '<div class="card-title" style="">';

                        echo sprintf('
                                <a class="truncate" href="kb2.php?cid=%d&sub_cat=1">%2$s</a> ',
                                $c->getId(),
                                $c->getLocalName(),
                                $c->getNumFAQs(),
                                $c->getVisibilityDescription()
                                );
                    echo "</div>";
                    echo '<div class="card-message" style="">';
                        echo sprintf('<p> <strong>Sub-Categories<strong> -  %3$s </p> <p> <strong>Visibility</strong> -  %4$s  </p> ',
                            $c->getId(),
                            $c->getLocalName(),
                            $c->getNumFAQs(),
                            $c->getVisibilityDescription()
                        );
                    echo '</div>';
                echo '</div>';
            echo '</div>';
                
        }

        echo '</div>';
    }

    if (!$thisstaff->hasPerm(Dept::PERM_DEPT)) {
        $staffTopics = $thisstaff->getTopicNames(false);
        $filter = true;
    }
    
    $faqs = $category->faqs
        ->constrain(array('attachments__inline' => 0))
        ->annotate(array('attachments' => SqlAggregate::COUNT('attachments')));
    if ($faqs->exists(true)) {
            /* Default VIEW DATA */
    
        echo '<div class="row">';
        foreach ($faqs as $faq) {
            if ($filter) {
                if ($faqTopics = $faq->getHelpTopicsIds()) {
                    foreach ($faqTopics as $key => $value) {
                        if (array_key_exists($value, $staffTopics))
                            $show = true;
                    }
                } else
                    $show = true;
            } else
                $show = true;
    
            if ($show){
                echo '<div class="col-md-3">';
                echo '<div class="card" style="">';
                    echo '<div class="text-center icon">
                        <i class="fas fa-folder-open"></i>
                    </div>';
                    echo '<div class="card-title" style="">';
                        echo sprintf('
                            <a  href="faq.php?id=%d" class="previewfaq truncate"> %2$s </a>',
                            $faq->getId(),
                            $faq->getQuestion(),
                            $faq->isPublished() ? __('Published'):__('Internal'),
                            $faq->attachments ? '<i class="icon-paperclip"></i>' : ''
                        );
                    echo '</div>';

                    echo '<div class="card-message" style="">';
                        echo sprintf('
                            <p> <strong>Status<strong> -  %3$s </p> <p> <p> %2$s </p>',
                            $faq->getId(),
                            $faq->getQuestion(),
                            $faq->isPublished() ? __('Published'):__('Internal'),
                            $faq->attachments ? '<i class="icon-paperclip"></i>' : ''
                        );
                    echo '</div>';
                echo '</div>';
            echo '</div>';
            }
            
        }
        echo '</div>';
    

    }

    ?>
</div>

</div>
