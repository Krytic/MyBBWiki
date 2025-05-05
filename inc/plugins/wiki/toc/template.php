<div class="toc">
<?php foreach($dataToc as $item): ?>
    <?php if ($item['list_open']): ?>
        <ol>
    <?php endif ?>
    <?php if ($item['item_open']): ?>
            <li>
    <?php endif ?>
    <?php if ($item['text']): ?>
                <a href="<?= $item['href'] ?>" title="<?= $item['text'] ?>"><?= $item['text'] ?></a>
    <?php endif ?>
    <?php if ($item['item_close']): ?>
            </li>
    <?php endif ?>
    <?php if ($item['list_close']): ?>
        </ol>
    <?php endif ?>
<?php endforeach; ?>

</div>