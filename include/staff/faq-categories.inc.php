

<?php
if(!defined('OSTSTAFFINC') || !$thisstaff) die('Access Denied');

?>
<form id="kbSearch" action="kb.php" method="get">
    <input type="hidden" name="a" value="search">
    <input type="hidden" name="cid" value="<?php echo Format::htmlchars($_REQUEST['cid']); ?>"/>
    <input type="hidden" name="topicId" value="<?php echo Format::htmlchars($_REQUEST['topicId']); ?>"/>

    <div id="basic_search">
        <div class="attached input">
        <!--osta-->
            <input id="query" type="text" size="20" name="q"
                placeholder="<?php echo __('Search FAQs Here'); ?>"
                value="<?php echo Format::htmlchars($_REQUEST['q']); ?>">
            <button class="attached button" id="searchSubmit" type="submit">
                <i class="icon icon-search"></i>
            </button>
        </div>

        <div class="pull-right">

            
        

            <span class="action-button muted" data-dropdown="#category-dropdown">
                <i class="icon-caret-down pull-right"></i>
                <span>
                    <i class="icon-filter"></i>
                    <?php echo __('Category'); ?>
                </span>
            </span>
            <span class="action-button muted" data-dropdown="#topic-dropdown">
                <i class="icon-caret-down pull-right"></i>
                <span>
                    <i class="icon-filter"></i>
                    <?php echo __('Help Topic'); ?>
                </span>
            </span>

            <span class="action-button">
                <span>
                   <a href="kb2.php" id="tileView"> <?php echo __('Tile View'); ?></a>
                </span>
            </span>

            <span class="action-button">
                <span>
                   <a href="kb1.php" id="tileView"> <?php echo __('Tab View'); ?></a>

                </span>
            </span>
        </div>

        <div id="category-dropdown" class="action-dropdown anchor-right"
            onclick="javascript:
                var form = $(this).closest('form');
                form.find('[name=cid]').val($(event.target).data('cid'));
                form.submit();">
            <ul class="bleed-left">
<?php
$total = FAQ::objects()->count();

$categories = Category::objects()
    ->annotate(array('faq_count' => SqlAggregate::COUNT('faqs')))
    ->filter(array('faq_count__gt' => 0))
    ->order_by('name')
    ->all();
array_unshift($categories, new Category(array('id' => 0, 'name' => __('All Categories'), 'faq_count' => $total)));
foreach ($categories as $C) {
        $active = $_REQUEST['cid'] == $C->getId(); ?>
        <li <?php if ($active) echo 'class="active"'; ?>>
            <a href="#" data-cid="<?php echo $C->getId(); ?>">
                <i class="icon-fixed-width <?php
                if ($active) echo 'icon-hand-right'; ?>"></i>
                <?php echo sprintf('%s (%d)',
                    Format::htmlchars($C->getFullName()),
                    $C->faq_count); ?></a>
        </li> <?php
} ?>
            </ul>
        </div>

        <div id="topic-dropdown" class="action-dropdown anchor-right"
            onclick="javascript:
                var form = $(this).closest('form');
                form.find('[name=topicId]').val($(event.target).data('topicId'));
                form.submit();">
            <ul class="bleed-left">
<?php
$topics = Topic::objects()
    ->annotate(array('faq_count'=>SqlAggregate::COUNT('faqs')))
    ->filter(array('faq_count__gt'=>0))
    ->all();
usort($topics, function($a, $b) {
    return strcmp($a->getFullName(), $b->getFullName());
});
array_unshift($topics, new Topic(array('id' => 0, 'topic' => __('All Topics'), 'faq_count' => $total)));
if (!$thisstaff->hasPerm(Dept::PERM_DEPT))
    $staffTopics = $thisstaff->getTopicNames(false);

foreach ($topics as $T) {
        $active = $_REQUEST['topicId'] == $T->getId();
        if (!$staffTopics || is_null($T->getId()) || ($staffTopics && array_key_exists($T->getId(), $staffTopics))) { ?>
        <li <?php if ($active) echo 'class="active"'; ?>>
            <a href="#" data-topic-id="<?php echo $T->getId(); ?>">
                <i class="icon-fixed-width <?php
                if ($active) echo 'icon-hand-right'; ?>"></i>
                <?php echo sprintf('%s (%d)',
                    Format::htmlchars($T->getFullName()),
                    $T->faq_count); ?></a>
        </li> <?php
        }
} ?>
            </ul>
        </div>

    </div>
</form>
    <div class="has_bottom_border" style="margin-bottom:5px; padding-top:5px;">
        <div class="pull-left">
            <h2><?php echo __('Frequently Asked Questions');?></h2>
        </div>
        <div class="clear"></div>
    </div>
<div>
<?php
if($_REQUEST['q'] || $_REQUEST['cid'] || $_REQUEST['topicId']) { //Search.
    $faqs = FAQ::objects()
        ->annotate(array(
            'attachment_count'=>SqlAggregate::COUNT('attachments'),
            'topic_count'=>SqlAggregate::COUNT('topics')
        ))
        ->order_by('question');

    if ($_REQUEST['cid'])
        $faqs->filter(array('category_id'=>$_REQUEST['cid']));

    if ($_REQUEST['topicId'])
        $faqs->filter(array('topics__topic_id'=>$_REQUEST['topicId']));

    if ($_REQUEST['q'])
        $faqs->filter(Q::ANY(array(
            'question__contains'=>$_REQUEST['q'],
            'answer__contains'=>$_REQUEST['q'],
            'keywords__contains'=>$_REQUEST['q'],
            'category__name__contains'=>$_REQUEST['q'],
            'category__description__contains'=>$_REQUEST['q'],
        )));

    echo "<div><strong>".__('Search Results')."</strong></div><div class='clear'></div>";
    if ($faqs->exists(true)) {
       
        echo '<div id="faq">
                <ol>';
        foreach ($faqs as $F) {
            echo sprintf(
                '<li><a href="faq.php?id=%d" class="previewfaq">%s</a> - <span>%s</span></li>',
                $F->getId(), $F->getLocalQuestion(), $F->getVisibilityDescription());
        }
        echo '  </ol>
             </div>';

        echo '<div id="faq" class="row">';
        foreach ($faqs as $F) {
            echo sprintf(
                '<div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title"><a href="faq.php?id=%d" class="previewfaq text-white">%s</a></h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><span>%s</span></p>
                        </div>
                    </div>
                </div>',
                $F->getId(), $F->getLocalQuestion(), $F->getVisibilityDescription()
            );
        }
        echo '</div>';


        echo '<ul id="faq" class="list-group">';  // Start of the list group
        foreach ($faqs as $F) {
            echo sprintf(
                '<li class="list-group-item">
                    <div class="faq-item">
                        <a href="faq.php?id=%d" class="faq-link">%s</a>
                        <ul class="list-group mt-2">  <!-- Nested list group for description -->
                            <li class="list-group-item"><span>%s</span></li>
                        </ul>
                    </div>
                </li>',
                $F->getId(), $F->getLocalQuestion(), $F->getVisibilityDescription()
            );
        }
        echo '</ul>';  // End of the list group
        

        
    } else {
        echo '<strong class="faded">'.__('The search did not match any FAQs.').'</strong>';
    }
} else {
     //Category Listing.
    $categories = Category::objects()
        ->annotate(array('faq_count'=>SqlAggregate::COUNT('faqs')))
        ->filter(array('category_pid__isnull' => true));


    /* Default View */

    if (count($categories)) {
        $categories->sort(function($a) { return $a->getLocalName(); });
        echo '<div>'.__('Click on the category to browse FAQs or manage its existing FAQs.').'</div>
                <ul id="kb">';
        foreach ($categories as $C) {
            echo sprintf('
                <li>
                    <h4><a class="truncate" style="max-width:600px" href="kb.php?cid=%d">%s (%d)</a> - <span>%s</span></h4>
                    %s ',
                $C->getId(),$C->getLocalName(),$C->getNumFAQs(),
                $C->getVisibilityDescription(),
                Format::safe_html($C->getLocalDescriptionWithImages())
            );
                if ($C->children) {
                    echo '<p/><div>';
                    foreach ($C->children as $c) {
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
            echo '</li>';
        }
        echo '</ul>';


        echo '<div class="mb-4 text-primary fw-bold">'.__('Click on the category to browse FAQs or manage its existing FAQs.').'</div>';
        echo '<div class="row">';
    
        foreach ($categories as $C) {
            echo '<div class="col-md-4 col-lg-4 mb-4">
                    <div class="card h-100 border-primary shadow-sm">
                        <div class="card-header bg-primary ">
                            <strong class="text-white">' . htmlspecialchars($C->getLocalName()) . ' (' . $C->getNumFAQs() . ')</strong>
                        </div>
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted">
                                <i class="bi bi-eye"></i> ' . htmlspecialchars($C->getVisibilityDescription()) . '
                            </h6>
                            <p class="card-text text-dark small">' . Format::safe_html($C->getLocalDescriptionWithImages()) . '</p>';
    
            if ($C->children) {
                echo '<div class="mt-3">
                        <strong class="text-secondary">Subcategories:</strong>';
                foreach ($C->children as $c) {
                    echo '<div class="ms-3">
                            <i class="bi bi-folder2-open text-primary"></i> 
                            <a href="kb.php?cid=' . $c->getId() . '" class="text-decoration-none fw-medium text-dark">
                                ' . htmlspecialchars($c->getLocalName()) . ' (' . $c->getNumFAQs() . ')
                            </a> 
                            - <span class="text-muted small">' . htmlspecialchars($c->getVisibilityDescription()) . '</span>
                        </div>';
                }
                echo '</div>';
            }
    
            echo '   </div> 
                    </div>
                </div>';
        }
    
        echo '</div>';



echo '<div class="mb-4 text-primary fw-bold">'.__('Click on the category to browse FAQs or manage its existing FAQs.').'</div>';
echo '<ul class="list-group">'; // List group starts here

foreach ($categories as $C) {
    // Main category item with border and some styling
    echo '<li class="list-group-item border-primary p-3 mb-3">'; 

    // Category Name and Info
    echo '<div class="d-flex justify-content-between">
            <div>
                <strong class="text-primary">' . htmlspecialchars($C->getLocalName()) . ' (' . $C->getNumFAQs() . ')</strong>
                <span class="text-muted small"> - ' . htmlspecialchars($C->getVisibilityDescription()) . '</span>
            </div>
            <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#categoryCollapse' . $C->getId() . '" aria-expanded="false" aria-controls="categoryCollapse' . $C->getId() . '">
                Toggle Description
            </button>
          </div>';

    // Category description
    echo '<div class="ms-3 text-dark small">' . Format::safe_html($C->getLocalDescriptionWithImages()) . '</div>';

    // If the category has subcategories, list them in a nested tree
    if ($C->children) {
        echo '<ul class="list-group mt-2" style="border-top: 2px solid #007bff;">'; // Nested list
        
        foreach ($C->children as $c) {
            echo '<li class="list-group-item border-secondary p-3 mb-2">
                    <div class="d-flex justify-content-between">
                        <div>
                            <i class="bi bi-folder2-open text-primary"></i>
                            <a href="kb.php?cid=' . $c->getId() . '" class="text-decoration-none fw-medium text-dark">
                                ' . htmlspecialchars($c->getLocalName()) . ' (' . $c->getNumFAQs() . ')
                            </a> 
                            - <span class="text-muted small">' . htmlspecialchars($c->getVisibilityDescription()) . '</span>
                        </div>
                        <button class="btn btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#subcategoryCollapse' . $c->getId() . '" aria-expanded="false" aria-controls="subcategoryCollapse' . $c->getId() . '">
                            Toggle Details
                        </button>
                    </div>
                    <div class="collapse" id="subcategoryCollapse' . $c->getId() . '">
                        <div class="ms-3 mt-2 text-dark small">
                            ' . Format::safe_html($c->getLocalDescriptionWithImages()) . '
                        </div>
                    </div>
                  </li>';
        }

        echo '</ul>'; // End of nested subcategory list
    }

    echo '</li>'; // Close main category item
}

echo '</ul>'; // Close list group





    } else {
        echo __('NO FAQs found');
    }

   
}
?>
</div>

