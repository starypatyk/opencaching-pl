<head>
    <link href="/tpl/smarty/medals.css" rel="stylesheet" type="text/css"/>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
</head>
<div class="mainContainer">
    <div class="headerDiv">{$user.userName}: {$smartyMedals.tr.medals} </div>
    <div class="medalWrapper">
        {foreach $smartyMedals.medals as $medal}
            <div class="medal">
                <a class="tooltips" href="#">
                <img src="{$medal.imgSrc}" class="medalImage" />
                <span class="tt">
                    <div class="tooltipHeader">{$smartyMedals.tr.medalInfo}:</div>
                    {foreach $medal.profile as $name => $info}
                        {if $name == cacheType}
                            {$smartyMedals.tr.cacheTypes}:
                            {foreach $info as $cachetype}
                                <img src="{$geocacheIcons[$cachetype].iconSet.1.iconSmall}">
                            {/foreach}
                        {else if $name == 'medalDescription'}
                            <div class="medalDescription">{$info} aa </div>
                        {else}
                            <div class="currentLevelInfo">{$name}: {$info} </div>
                        {/if}
                    {/foreach}
                    <hr>
                    <div class="tooltipHeader">{$smartyMedals.tr.currentLevelRequirements}:</div>
                    {foreach $medal.currentLevelInfo as $name => $info}
                        <div class="currentLevelInfo">{$name}: {$info} </div>
                    {/foreach}
                    <hr>
                    <div class="tooltipHeader">{$smartyMedals.tr.nextLevelRequirements}:</div>
                    {foreach $medal.nextLevelInfo as $name => $info}
                        <div class="nextLevelInfo">{$name}: {$info} </div>
                    {/foreach}
                </span></a><br>
                <div class="medalName">
                    {$medal.name}
                </div>
                <div class="medalLevel">
                    {$smartyMedals.tr.level}: {$medal.level} {$medal.levelName}
                </div>
            </div>
        {/foreach}
    </div>

</div>