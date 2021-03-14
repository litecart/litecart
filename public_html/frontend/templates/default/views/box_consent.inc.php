<style>
<?php ob_start(); ?>
.box.consent {
  padding: 0;
  background: #f9f9f9;
  border: 1px solid rgba(0,0,0,0.1);
  border-radius: 4px;
}
.box.consent .title {
  font-weight: bold;
  font-size: 1.2em;
}
.box.consent .description {
  margin-bottom: 15px;
  background: #fff;
  padding: 15px;
  max-height: 200px;
  overflow-y: auto;
  border-bottom: 1px solid rgba(0,0,0,0.1);
}
.box.consent .description > :first-child {
  margin-top: 0;
}
.box.consent .description > :last-child {
  margin-bottom: 0;
}
.box.consent .opt-ins {
  margin-bottom: 15px;
}
.box.consent .opt-ins label {
  font-weight: bold;
}
.box.consent .opt-ins input[type="checkbox"][required]:not(:checked) {
  animation: flasher 1s linear infinite;
}
.box.consent .previous-consent {
  background: #eee;
  padding: 7.5px;
  margin: 7.5px 0;
}
<?php document::$snippets['style']['box.consent'] = ob_get_clean(); ?>
</style>

<div class="box consent">
  <!--<div class="title">{{title}}</div>-->

  <?php if ($description) { ?>
  <div class="description">
    {{description}}
  </div>
  <?php } ?>

  <ul class="opt-ins list-unstyled">
    <?php foreach ($opt_ins as $opt_in) { ?>
    <li>
      <label class="checkbox">
        <?php echo functions::form_draw_checkbox($type, '1', !empty($opt_in['previous_consent']) ? '1' : true, !empty($opt_in['required']) ? 'required' : ''); ?> <?php echo $opt_in['description']; ?>
        <?php //echo !empty($opt_in['required']) ? '<span class="required">*</span>' : ''; ?>
      </label>

      <?php if (!empty($opt_in['previous_consent'])) { ?>
      <div class="previous-consent text-center"><em><?php echo $opt_in['previous_consent']['formatted']; ?></em></div>
      <?php } ?>
    </li>
    <?php } ?>
  </ul>

</div>
