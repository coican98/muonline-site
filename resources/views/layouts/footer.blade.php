<footer>
    @php
        $settingsPath = storage_path('app/settings.json');
        $sData = [];
        if (file_exists($settingsPath)) {
            $sData = json_decode(file_get_contents($settingsPath), true);
        }
        
        $discordUrl = isset($sData['discord_url']) ? $sData['discord_url'] : 'https://discord.gg/zAHb9Hq7';
        $whatsappUrl = isset($sData['whatsapp_url']) ? $sData['whatsapp_url'] : 'https://chat.whatsapp.com/EM3NYi43D1KJsE2m8hX5Om';
    @endphp

    <div>
        @if(!empty(trim($discordUrl)))
        <a target="_blank" href="{{ $discordUrl }}" rel="noopener noreferrer" class="footer-icon-wrap" aria-label="Discord">
            <i class="fab fa-discord" aria-hidden="true"></i>
        </a>
        @endif
        @if(!empty(trim($whatsappUrl)))
        <a target="_blank" href="{{ $whatsappUrl }}" rel="noopener noreferrer" class="footer-icon-wrap" aria-label="WhatsApp">
            <i class="fab fa-whatsapp" aria-hidden="true"></i>
        </a>
        @endif
    </div>
    <div>
        <p>
            nsL &copy; {{ date('Y') }} Todos os direitos reservados.
        </p>
    </div>
</footer>
