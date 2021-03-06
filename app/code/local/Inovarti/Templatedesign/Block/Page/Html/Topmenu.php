<?php

/**
 *
 * @category   Inovarti
 * @package    Inovarti_Templatedesign
 * @author     Suporte <suporte@inovarti.com.br>
 */
class Inovarti_Templatedesign_Block_Page_Html_Topmenu extends Mage_Page_Block_Html_Topmenu {
    
    /**
     * Recursively generates top menu html from data that is specified in $menuTree
     *
     * @param Varien_Data_Tree_Node $menuTree
     * @param string $childrenWrapClass
     * @return string
     */
    protected function _getHtml(Varien_Data_Tree_Node $menuTree, $childrenWrapClass) {
        $html = '';

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = is_null($parentLevel) ? 0 : $parentLevel + 1;

        $counter = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

        foreach ($children as $child) {

            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);

            $anchorClasses = array();
            $anchorAttributes = array();

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel == 0 && $outermostClass) {
                $anchorClasses[] = $outermostClass;
                $child->setClass($outermostClass);
            }

            if ($child->hasChildren()) {
                $anchorClasses[] = 'dropdown-toggle';

                if ($childLevel == 0) {
                    // $anchorAttributes[] = array('data-toggle', 'dropdown');
                    $anchorAttributes[] = array('data-hover', 'dropdown');
                }
            }

            // Add list item
            $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';

            
            array_walk($anchorAttributes, 
                    function(&$attribute) {
                        $attribute = $attribute[0] . '="' . $attribute[1] . '"';
                    });

            // Add anchor item
            $html .= '<a href="' . $child->getUrl() . '" class="' . implode(' ', $anchorClasses) . '"' . implode(' ', $anchorAttributes) . '>';

            // Actual item itself
            $html .= '<span>' . $this->escapeHtml($child->getName()) . '</span>';

            if ($child->hasChildren() && $childLevel == 0) {
                //$html .= '<span class="caret"></span>';
            }

            // Close anchor item
            $html .= '</a>';
            /*
              if ($child->hasChildren()) {
              $html .= '<ul class="level' . $childLevel . ' ' . $childrenWrapClass . '">';
              $html .= $this->_getHtml($child, $childrenWrapClass);
              $html .= '</ul>';
              } */
            $html .= '</li>';

            $counter++;
        }

        return $html;
    }

    public function getHtmlDesktop($outermostClass = '', $childrenWrapClass = '') {
        Mage::dispatchEvent('page_block_html_topmenu_gethtml_before', array(
            'menu' => $this->_menu,
            'block' => $this
        ));

        $this->_menu->setOutermostClass($outermostClass);
        $this->_menu->setChildrenWrapClass($childrenWrapClass);

        $html = $this->_getHtmlDesktop($this->_menu, $childrenWrapClass);

        Mage::dispatchEvent('page_block_html_topmenu_gethtml_after', array(
            'menu' => $this->_menu,
            'html' => $html
        ));

        return $html;
    }

    protected function _getHtmlDesktop(Varien_Data_Tree_Node $menuTree, $childrenWrapClass) {
        $html = '';

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = is_null($parentLevel) ? 0 : $parentLevel + 1;

        $counter = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $childrenWrapClass .' ' . $parentPositionClass : $childrenWrapClass;
        
        $column_items = Mage::getStoreConfig('templatedesign/navigation/column_items');

                
        foreach ($children as $child) {

            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();
            
            $allCategory = '';
            
            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="nav-menu-item-link" ';
                $child->setClass($outermostClass);
            }
            if ($child->hasChildren() && $childLevel > 0) {
                $outermostClassCode = '';
            }
            
            $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
            $html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '>';
            
            if ($childLevel == 0) {
                $allCategory = '<a href="' . $child->getUrl() . '" class="footer clearfix">Veja toda a loja de '.$this->escapeHtml($child->getName()).'</a>';
                if ($img = $this->getThumbnailUrl($child)){
                    $html .= '<span class="icon"><img src="'.$img.'" alt="'.$this->escapeHtml(strtolower($child->getName())).'" /></span>';
                }else{
                    //$html .= '<span class="icon"><i class="fa '.$this->escapeHtml(strtolower($child->getName())).'"></i></span>';
                    $html .= '<span class="icon"><i class="fa fa-mobile-phone"></i></span>';
                }
            }
            $html .= $this->escapeHtml($child->getName()) .'</a>';

            if ($child->hasChildren()) {
                $html .= '<div class="nav-submenu nav-submenu-default">';
                $html .= '  <div class="nav-submenu-border col-md-12">';
                $html .= '      <ul class="categories col-md-6">';
                $html .=            $this->_getHtmlDesktop($child, 'item');
                $html .= '      </ul>';
                
                $staticBlock = trim($this->getLayout()->createBlock('cms/block')->setBlockId(strtolower($child->getData('url_key')))->toHtml());
                if (!empty($staticBlock)) {
                   $html .= '<span class="banner-item col-md-6">';
                    $html .= $staticBlock;
                    $html .= '</span>';
                }
                $html .=        $allCategory;
                $html .= '  </div>';
                $html .= '</div>';
            }
            $html .= '</li>';

            if ($childLevel==1){
                if (is_numeric($column_items) && $counter ==$column_items){
                    break;
                }
            }

            $counter++;
        }

        return $html;
    }
    public function getHtmlAllDesktop($outermostClass = '', $childrenWrapClass = '') {
        Mage::dispatchEvent('page_block_html_topmenu_gethtml_before', array(
            'menu' => $this->_menu,
            'block' => $this
        ));

        $this->_menu->setOutermostClass($outermostClass);
        $this->_menu->setChildrenWrapClass($childrenWrapClass);

        $html = $this->_getHtmlAllDesktop($this->_menu, $childrenWrapClass);

        Mage::dispatchEvent('page_block_html_topmenu_gethtml_after', array(
            'menu' => $this->_menu,
            'html' => $html
        ));

        return $html;
    }
    protected function _getHtmlAllDesktop(Varien_Data_Tree_Node $menuTree, $childrenWrapClass) {
        $html = '';

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = is_null($parentLevel) ? 0 : $parentLevel + 1;

        $counter = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $childrenWrapClass .' ' . $parentPositionClass : $childrenWrapClass;
        $column_items = Mage::getStoreConfig('templatedesign/navigation/column_items') /2;

        foreach ($children as $child) {

            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();
            
            if ($child->hasChildren() && $childLevel > 0) {
                $outermostClassCode = '';
            }
             if ($childLevel==0){
                 $html .= '<div class="col-md-4">';
                 $html .= ' <dl class="list">';
                 $html .= ' <dt class="title">'.$this->escapeHtml($child->getName()).'</dt>';
             }

            //$html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
            //$html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '>';

            //$html .= $this->escapeHtml($child->getName()) .'</a>';
             if ($childLevel==1){
                $html .= '<dd class="' . $childrenWrapClass . '"><a href="' . $child->getUrl() . '">'.$this->escapeHtml($child->getName()).'</a></dd>';
             }
            if ($child->hasChildren()) {
                $html .=   $this->_getHtmlAllDesktop($child, 'item');
            }
            if ($childLevel==0){
            $html .= '<hr class="separator"></dl></div>';
            }
            if ($childLevel==1){
                if (is_numeric($column_items) && $counter ==$column_items){
                    break;
                }
            }
            $counter++;
        }

        return $html;
    }
    /**
     * Returns array of menu item's classes
     *
     * @param Varien_Data_Tree_Node $item
     * @return array
     */
    
    protected function _getMenuItemClasses(Varien_Data_Tree_Node $item)
    {
        $classes = array();

        //$classes[] = 'level' . $item->getLevel();
        $classes[] = $item->getPositionClass();

        if ($item->getIsFirst()) {
            $classes[] = 'first';
        }

        if ($item->getIsActive()) {
            $classes[] = 'active';
        }

        if ($item->getIsLast()) {
            $classes[] = 'last';
        }

        if ($item->getClass()) {
            $classes[] = $item->getClass();
        }

        if ($item->hasChildren()) {
            $classes[] = 'parent';
        }

        return $classes;
    }
    /*
    protected function _getMenuItemClasses(Varien_Data_Tree_Node $item) {
        $classes = parent::_getMenuItemClasses($item);

        if ($item->hasChildren()) {

            if ($item->getLevel() == 0) {
                $classes[] = 'dropdown';
            } else {
                $classes[] = 'dropdown-menu';
            }
        }

        return $classes;
    }*/
    public function getThumbnailUrl($child)
      {
          $url = false;
          if ($image = $child->getData('thumbnail')) {
              $url = Mage::getBaseUrl('media').'catalog/category/'.$image;
          }
          return $url;
      }
      
}
