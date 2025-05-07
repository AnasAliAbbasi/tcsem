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
    <div class="cat-desc has_bottom_border">
    <?php echo Format::display($category->getDescription());
    if ($category->children) {
        echo '<p/><div>';
        foreach ($category->children as $c) {
            echo sprintf('<div><i class="icon-folder-open-alt"></i>
                    <a href="kb.php?cid=%d">%s (%d)</a> - <span>%s</span></div>',
                    $c->getId(),
                    $c->getLocalName(),
                    $c->getNumFAQs(),
                    $c->getVisibilityDescription()
                    );
        }
        echo '</div>';
    }
    ?>
</div>
<?php
if (!$thisstaff->hasPerm(Dept::PERM_DEPT)) {
    $staffTopics = $thisstaff->getTopicNames(false);
    $filter = true;
}

$faqs = $category->faqs
    ->constrain(array('attachments__inline' => 0))
    ->annotate(array('attachments' => SqlAggregate::COUNT('attachments')));
if ($faqs->exists(true)) {
        /* Default VIEW DATA */

    echo '<div id="faq">
            <ol>';
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

        if ($show)
        echo sprintf('
            <li><strong><a href="faq.php?id=%d" class="previewfaq">%s <span>- %s</span></a> %s</strong></li>',
            $faq->getId(),$faq->getQuestion(),$faq->isPublished() ? __('Published'):__('Internal'),
            $faq->attachments ? '<i class="icon-paperclip"></i>' : ''
        );
    }
    echo '  </ol>
         </div>';

    /* Default VIEW DATA */
        

    /* TILE VIEW DATA */

    echo '<div id="faq" class="row">';
foreach ($faqs as $faq) {
    $show = false; // Make sure the $show variable is initialized to avoid undefined variable errors
    if ($filter) {
        if ($faqTopics = $faq->getHelpTopicsIds()) {
            foreach ($faqTopics as $key => $value) {
                if (array_key_exists($value, $staffTopics))
                    $show = true;
            }
        } else {
            $show = true;
        }
    } else {
        $show = true;
    }

    if ($show) {
        echo sprintf('
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title">
                            <a href="faq.php?id=%d" class="previewfaq text-white">%s</a>
                        </h5>
                    </div>
                    <div class="card-body">
                        <p><strong>%s</strong></p>
                        <p>%s</p>
                        %s
                    </div>
                </div>
            </div>',
            $faq->getId(),
            $faq->getQuestion(),
            $faq->isPublished() ? __('Published') : __('Internal'),
            $faq->isPublished() ? __('Published') : __('Internal'),
            $faq->attachments ? '<i class="icon-paperclip"></i>' : ''
        );
    }
}
echo '</div>';

/* TILE VIEW DATA */

/* tree View */

// echo '<div id="faq" class="list-group">'; // Start of the list-group container

// foreach ($faqs as $faq) {
//     $show = false; // Initialize show variable

//     // Apply filtering logic
//     if ($filter) {
//         if ($faqTopics = $faq->getHelpTopicsIds()) {
//             foreach ($faqTopics as $key => $value) {
//                 if (array_key_exists($value, $staffTopics))
//                     $show = true;
//             }
//         } else {
//             $show = true;
//         }
//     } else {
//         $show = true;
//     }

//     if ($show) {
//         // Generating each FAQ item as a list-group item
//         echo sprintf('
//             <div class="list-group-item list-group-item-action mb-3 border-primary bg-light">
//                 <h5 class="mb-1">
//                     <a href="faq.php?id=%d" class="previewfaq text-primary">%s</a>
//                 </h5>
//                 <p class="mb-1">
//                     <strong>%s</strong>
//                     %s
//                 </p>
//                 <small class="text-muted">%s</small>
//                 <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse%d" aria-expanded="false" aria-controls="faqCollapse%d">
//                     Toggle Details
//                 </button>
//                 <div class="collapse" id="faqCollapse%d">
//                     <p>%s</p>
//                 </div>
//             </div>',
//             $faq->getId(),
//             $faq->getQuestion(),
//             $faq->isPublished() ? __('Published') : __('Internal'),
//             $faq->attachments ? '<i class="icon-paperclip"></i>' : '',
//             $faq->isPublished() ? __('Published') : __('Internal'),
//             $faq->getId(), $faq->getId(), $faq->getId(),
//             $faq->getVisibilityDescription()
//         );
//     }
// }

// echo '</div>'; // End of the list-group container

echo '<ul id="faq" class="list-group">'; // Start of the list-group container

foreach ($faqs as $faq) {
    $show = false; // Initialize show variable

    // Apply filtering logic
    if ($filter) {
        if ($faqTopics = $faq->getHelpTopicsIds()) {
            foreach ($faqTopics as $key => $value) {
                if (array_key_exists($value, $staffTopics))
                    $show = true;
            }
        } else {
            $show = true;
        }
    } else {
        $show = true;
    }

    if ($show) {
        // Start the nested list item (tree structure)
        echo sprintf('
            <li class="list-group-item border-primary p-3 mb-3">
                <div class="d-flex justify-content-between">
                    <div class="faq-header">
                        <h5 class="mb-1">
                            <a href="faq.php?id=%d" class="previewfaq text-primary">%s</a>
                        </h5>
                        <p class="mb-1">
                            <strong>%s</strong>
                            %s
                        </p>
                        <small class="text-muted">%s</small>
                    </div>
                    <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse%d" aria-expanded="false" aria-controls="faqCollapse%d">
                        Toggle Details
                    </button>
                </div>

                <ul class="list-group mt-2" style="border-top: 2px solid #007bff;"> <!-- Nested list -->
                    <li class="list-group-item">
                        <strong>Additional Info:</strong>
                        <p>%s</p>
                    </li>
                    <li class="list-group-item">
                        <strong>Attachments:</strong>
                        %s
                    </li>
                    <li class="list-group-item">
                        <strong>Visibility:</strong>
                        %s
                    </li>
                </ul>

                <div class="collapse" id="faqCollapse%d">
                    <p>%s</p>
                </div>
            </li>',
            $faq->getId(),
            $faq->getQuestion(),
            $faq->isPublished() ? __('Published') : __('Internal'),
            $faq->attachments ? '<i class="icon-paperclip"></i>' : '',
            $faq->isPublished() ? __('Published') : __('Internal'),
            $faq->getId(), $faq->getId(), $faq->getId(),
            $faq->getVisibilityDescription(),
            $faq->attachments ? '<i class="icon-paperclip"></i>' : '', // Attachments section
            $faq->isPublished() ? __('Published') : __('Internal'), // Visibility section
            $faq->getId(), $faq->getVisibilityDescription()
        );
    }
}

echo '</ul>'; // End of list-group container


/* tree View */

} elseif (!$category->children) {
    echo '<strong>'.__('Category does not have FAQs').'</strong>';
}

/* tree View */
?>
</div>
