<span class="audio-definition" lang="$LangAttr">
    <% if $LinkToAudioFile %><button id="audiodef-trigger-{$ID}" aria-controls="audiodef-player-{$ID}" type="button" aria-label="pronounce"><span>&gt;</span></button><% end_if %>
    $Content
</span>
<% if $LinkToAudioFile %>
    <audio id="audiodef-player-{$ID}" crossorigin="anonymous">
        <source src="$LinkToAudioFile" type="audio/mpeg">
    </audio>
    <script type="text/javascript">
        const trigger = document.getElementById('audiodef-trigger-{$ID}');
        trigger.addEventListener('click', function() {
            const target = document.getElementById('audiodef-player-{$ID}');
            target.play();
        })
    </script>
<% end_if %>