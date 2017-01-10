<div id="filter-bar" class="btn-toolbar">
  <div class="filter-search btn-group pull-left">
    <label for="filter_search" class="element-invisible"><?php echo JText::_('COM_CONTACT_FILTER_SEARCH_DESC');?></label>
    <input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText('COM_CONTACT_SEARCH_IN_NAME'); ?>" />
  </div>
  <div class="btn-group pull-left">
    <button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
    <button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
  </div>
  <div class="btn-group pull-right hidden-phone">
    <label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
    <?php echo $this->pagination->getLimitBox(); ?>
  </div>
  <div class="btn-group pull-right hidden-phone">
    <label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC');?></label>
    <select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
      <option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
      <option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING');?></option>
      <option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');?></option>
    </select>
  </div>
  <div class="btn-group pull-right">
    <label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY');?></label>
    <select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
      <option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
      <?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder);?>
    </select>
  </div>
</div>

