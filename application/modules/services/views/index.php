<div id="headerbar">

    <h1 class="headerbar-title"><?php _trans('services'); ?></h1>

    <div class="headerbar-item pull-right">
        <button type="button" class="btn btn-default btn-sm submenu-toggle hidden-lg"
                data-toggle="collapse" data-target="#ip-submenu-collapse">
            <i class="fa fa-bars"></i> <?php _trans('submenu'); ?>
        </button>
        <a class="create-service btn btn-sm btn-primary" href="#">
            <i class="fa fa-plus"></i> <?php _trans('new'); ?>
        </a>
    </div>

    <div class="headerbar-item pull-right visible-lg">
        <?php echo pager(site_url('services/status/' . $this->uri->segment(3)), 'mdl_services'); ?>
    </div>

    <div class="headerbar-item pull-right visible-lg">
        <div class="btn-group btn-group-sm index-options">
            <a href="<?php echo site_url('services/status/all'); ?>"
               class="btn <?php echo $status == 'all' ? 'btn-primary' : 'btn-default' ?>">
                <?php _trans('all'); ?>
            </a>
            <a href="<?php echo site_url('services/status/draft'); ?>"
               class="btn <?php echo $status == 'draft' ? 'btn-primary' : 'btn-default' ?>">
                <?php _trans('draft'); ?>
            </a>
            <a href="<?php echo site_url('services/status/sent'); ?>"
               class="btn <?php echo $status == 'sent' ? 'btn-primary' : 'btn-default' ?>">
                <?php _trans('sent'); ?>
            </a>
            <a href="<?php echo site_url('services/status/approved'); ?>"
               class="btn <?php echo $status == 'approved' ? 'btn-primary' : 'btn-default' ?>">
                <?php _trans('approved'); ?>
            </a>
            <a href="<?php echo site_url('services/status/rejected'); ?>"
               class="btn <?php echo $status == 'rejected' ? 'btn-primary' : 'btn-default' ?>">
                <?php _trans('rejected'); ?>
            </a>
            <a href="<?php echo site_url('services/status/canceled'); ?>"
               class="btn <?php echo $status == 'canceled' ? 'btn-primary' : 'btn-default' ?>">
                <?php _trans('canceled'); ?>
            </a>
        </div>
    </div>

</div>

<div id="submenu">
    <div class="collapse clearfix" id="ip-submenu-collapse">

        <div class="submenu-row">
            <?php echo pager(site_url('services/status/' . $this->uri->segment(3)), 'mdl_services'); ?>
        </div>

        <div class="submenu-row">
            <div class="btn-group btn-group-sm index-options">
                <a href="<?php echo site_url('services/status/all'); ?>"
                   class="btn <?php echo $status == 'all' ? 'btn-primary' : 'btn-default' ?>">
                    <?php _trans('all'); ?>
                </a>
                <a href="<?php echo site_url('services/status/draft'); ?>"
                   class="btn <?php echo $status == 'draft' ? 'btn-primary' : 'btn-default' ?>">
                    <?php _trans('draft'); ?>
                </a>
                <a href="<?php echo site_url('services/status/sent'); ?>"
                   class="btn <?php echo $status == 'sent' ? 'btn-primary' : 'btn-default' ?>">
                    <?php _trans('sent'); ?>
                </a>
                <a href="<?php echo site_url('services/status/viewed'); ?>"
                   class="btn <?php echo $status == 'viewed' ? 'btn-primary' : 'btn-default' ?>">
                    <?php _trans('viewed'); ?>
                </a>
                <a href="<?php echo site_url('services/status/approved'); ?>"
                   class="btn <?php echo $status == 'approved' ? 'btn-primary' : 'btn-default' ?>">
                    <?php _trans('approved'); ?>
                </a>
                <a href="<?php echo site_url('services/status/rejected'); ?>"
                   class="btn <?php echo $status == 'rejected' ? 'btn-primary' : 'btn-default' ?>">
                    <?php _trans('rejected'); ?>
                </a>
                <a href="<?php echo site_url('services/status/canceled'); ?>"
                   class="btn <?php echo $status == 'canceled' ? 'btn-primary' : 'btn-default' ?>">
                    <?php _trans('canceled'); ?>
                </a>
            </div>
        </div>

    </div>
</div>

<div id="content" class="table-content">

    <div id="filter_results">
        <?php $this->layout->load_view('services/partial_service_table', array('services' => $services)); ?>
    </div>

</div>
