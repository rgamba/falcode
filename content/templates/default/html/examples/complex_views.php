<h1>Complex views example</h1>
<h2>Simple array: colors</h2>
<ul>
    <?php foreach($colors as $color): ?>
    <li><?php echo $color; ?></li>
    <?php endforeach; ?>
</ul>
<hr>
<h2>Complex array: Contacts</h2>
<br>
<table>
    <thead>
        <tr>
            <td style="width: 200px; font-weight: bold">Name</td>
            <td style="font-weight: bold">Phone</td>
        </tr>
    </thead>
    <?php foreach($contacts as $contact): ?>
    <tr>
        <td><?php echo $contact['name']; ?></td>
        <td><?php echo $contact['phone']; ?></td>
    </tr>
    <?php endforeach; ?>
</table>