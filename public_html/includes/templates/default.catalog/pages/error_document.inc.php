<style>
#box-error-document .code {
  font-size: 64px;
  font-weight: bold;
}
#box-error-document .title {
  font-size: 48px;
}
#box-error-document .description {
  font-size: 24px;
}
</style>

<div class="fourteen-forty">
  <main id="content">
    {snippet:notices}

    <article id="box-error-document" class="text-center">
      <div class="code">HTTP <?php echo $code; ?></div>
      <div class="title"><?php echo $title; ?></div>
      <p class="description"><?php echo $description; ?></p>
    </article>
  </main>
</div>