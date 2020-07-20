<?php

namespace Stickee\Sync\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stickee\Sync\Interfaces\CommissionInterface;
use Stickee\Sync\Interfaces\PropertyConfigurationInterface;
use Stickee\Sync\Models\Affiliate;
use Stickee\Sync\Models\AffiliateBasePropertyGroup;
use Stickee\Sync\Models\BaseProperty;
use Stickee\Sync\Models\BasePropertyGroup;
use Stickee\Sync\Models\Commissions\FixedCommission;
use Stickee\Sync\Models\Commissions\PercentageCommission;
use Stickee\Sync\Models\Property;
use Stickee\Sync\Models\PropertyConfigurations\BroadbandSite;
use Stickee\Sync\Models\PropertyConfigurations\MobilesSite;
use Stickee\Sync\Models\PropertyConfigurations\PetInsuranceSite;
use Stickee\Sync\Models\Theme;
use Stickee\Sync\Models\Vertical;

class Sync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:create-property';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a property and associated records';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            $affiliate = $this->getAffiliate();
            $vertical = $this->getVertical();
            $basePropertyGroup = $this->getBasePropertyGroup($vertical);
            $affiliateBasePropertyGroup = $this->getAffiliateBasePropertyGroup($affiliate, $basePropertyGroup);
            $baseProperty = $this->getBaseProperty($basePropertyGroup);

            $this->createProperty($baseProperty, $affiliateBasePropertyGroup);
        });
    }

    /**
     * Get an affiliate
     *
     * @return \Stickee\Sync\Models\Affiliate
     */
    private function getAffiliate(): Affiliate
    {
        $sync = Affiliate::all();
        $options = ['new' => '<Create new>'];

        $sync->each(function ($item) use (&$options) {
            $options[$item->id] = $item->id . ': ' . $item->name;
        })->all();

        if (count($options) === 1) {
            return $this->createAffiliate();
        }

        $selectedOption = $this->choice('Choose an affiliate or create a new one', $options, 'new');

        if ($selectedOption === 'new') {
            return $this->createAffiliate();
        }

        return $sync->find($selectedOption);
    }

    /**
     * Create an affiliate
     *
     * @return \Stickee\Sync\Models\Affiliate
     */
    private function createAffiliate(): Affiliate
    {
        $this->info('Create Affiliate');

        $affiliate = new Affiliate();
        $affiliate->name = $this->askRequired('Name?');
        $affiliate->code = Str::slug($affiliate->name);
        $affiliate->theme_id = optional($this->getTheme())->id;
        $affiliate->save();

        return $affiliate;
    }

    /**
     * Get a theme or null
     *
     * @return ?\Stickee\Sync\Models\Theme
     */
    private function getTheme(): ?Theme
    {
        $options = ['none' => '<None>', 'new' => '<Create new>'];

        $selectedOption = $this->choice('Theme?', $options, 'none');

        if ($selectedOption === 'new') {
            return $this->createTheme();
        }

        return null;
    }

    /**
     * Create a theme
     *
     * @return \Stickee\Sync\Models\Theme
     */
    private function createTheme(): Theme
    {
        $this->info('Create Theme');

        $theme = new Theme();
        $theme->logo_url = $this->askRequired('logo_url', 'https://placekitten.com/100/100');
        $theme->primary_color = $this->askRequired('primary_color', '#cc0000');
        $theme->primary_negative_color = $this->askRequired('primary_negative_color', '#9999cc');
        $theme->primary_accent_color = $this->askRequired('primary_accent_color', '#ff0000');
        $theme->primary_accent_negative_color = $this->askRequired('primary_accent_negative_color', '#ccccff');
        $theme->secondary_color = $this->askRequired('secondary_color', '#00cc00');
        $theme->secondary_negative_color = $this->askRequired('secondary_negative_color', '#9999cc');
        $theme->secondary_accent_color = $this->askRequired('secondary_accent_color', '#00ff00');
        $theme->secondary_accent_negative_color = $this->askRequired('secondary_accent_negative_color', '#ccccff');
        $theme->black_color = $this->askRequired('black_color', '#000000');
        $theme->grey_dark_color = $this->askRequired('grey_dark_color', '#333333');
        $theme->grey_medium_dark_color = $this->askRequired('grey_medium_dark_color', '#666666');
        $theme->grey_medium_light_color = $this->askRequired('grey_medium_light_color', '#999999');
        $theme->grey_light_color = $this->askRequired('grey_light_color', '#cccccc');
        $theme->white_color = $this->askRequired('white_color', '#ffffff');
        $theme->success_color = $this->askRequired('success_color', '#66cc66');
        $theme->info_color = $this->askRequired('info_color', '#6666cc');
        $theme->warning_color = $this->askRequired('warning_color', '#ff8000');
        $theme->danger_color = $this->askRequired('danger_color', '#dd0000');
        $theme->background_color = $this->askRequired('background_color', '#f0f0f0');
        $theme->body_color = $this->askRequired('body_color', '#666666');
        $theme->heading_color = $this->askRequired('heading_color', '#333333');
        $theme->body_font = $this->askRequired('body_font', 'Arial, sans-serif');
        $theme->heading_font = $this->askRequired('heading_font', '"Times New Roman", serif');
        $theme->save();

        return $theme;
    }

    /**
     * Get a vertical
     *
     * @return \Stickee\Sync\Models\Vertical
     */
    private function getVertical(): Vertical
    {
        $verticals = Vertical::all();
        $options = ['new' => '<Create new>'];

        $verticals->each(function ($item) use (&$options) {
            $options[$item->id] = $item->id . ': ' . $item->name;
        })->all();

        if (count($options) === 1) {
            return $this->createVertical();
        }

        $selectedOption = $this->choice('Choose a vertical or create a new one', $options, 'new');

        if ($selectedOption === 'new') {
            return $this->createVertical();
        }

        return $verticals->find($selectedOption);
    }

    /**
     * Create a vertical
     *
     * @return \Stickee\Sync\Models\Vertical
     */
    private function createVertical(): Vertical
    {
        $this->info('Create Vertical');

        $vertical = new Vertical();
        $vertical->name = $this->askRequired('Name?');
        $vertical->code = Str::slug($vertical->name);
        $vertical->save();

        return $vertical;
    }

    /**
     * Get a base property group
     *
     * @param \Stickee\Sync\Models\Vertical $vertical The vertical
     *
     * @return \Stickee\Sync\Models\BasePropertyGroup
     */
    private function getBasePropertyGroup(Vertical $vertical): BasePropertyGroup
    {
        $basePropertyGroups = BasePropertyGroup::where('vertical_id', $vertical->id)
            ->orderBy('name')
            ->get();
        $options = ['new' => '<Create new>'];

        $basePropertyGroups->each(function ($item) use (&$options) {
            $options[$item->id] = $item->id . ': ' . $item->name;
        })->all();

        if (count($options) === 1) {
            return $this->createBasePropertyGroup($vertical);
        }

        $selectedOption = $this->choice('Choose a base property group or create a new one', $options, 'new');

        if ($selectedOption === 'new') {
            return $this->createBasePropertyGroup($vertical);
        }

        return $basePropertyGroups->find($selectedOption);
    }

    /**
     * Create a base property group
     *
     * @param \Stickee\Sync\Models\Vertical $vertical The vertical
     *
     * @return \Stickee\Sync\Models\BasePropertyGroup
     */
    private function createBasePropertyGroup(Vertical $vertical): BasePropertyGroup
    {
        $this->info('Create BasePropertyGroup');

        $basePropertyGroup = new BasePropertyGroup();
        $basePropertyGroup->vertical_id = $vertical->id;
        $basePropertyGroup->name = $this->askRequired('Name?');

        $commission = $this->getCommission(true);
        $basePropertyGroup->commissionable()->associate($commission);

        $basePropertyGroup->save();

        return $basePropertyGroup;
    }

    /**
     * Get a base property group
     *
     * @param \Stickee\Sync\Models\Affiliate $affiliate The affiliate
     * @param \Stickee\Sync\Models\BasePropertyGroup $basePropertyGroup The base property group
     *
     * @return \Stickee\Sync\Models\AffiliateBasePropertyGroup
     */
    private function getAffiliateBasePropertyGroup(Affiliate $affiliate, BasePropertyGroup $basePropertyGroup): AffiliateBasePropertyGroup
    {
        $affiliateBasePropertyGroup = AffiliateBasePropertyGroup::where('affiliate_id', $affiliate->id)
            ->where('base_property_group_id', $basePropertyGroup->id)
            ->first();

        if ($affiliateBasePropertyGroup) {
            return $affiliateBasePropertyGroup;
        }

        return $this->createAffiliateBasePropertyGroup($affiliate, $basePropertyGroup);
    }

    /**
     * Create a base property group
     *
     * @param \Stickee\Sync\Models\Affiliate $affiliate The affiliate
     * @param \Stickee\Sync\Models\BasePropertyGroup $basePropertyGroup The base property group
     *
     * @return \Stickee\Sync\Models\AffiliateBasePropertyGroup
     */
    private function createAffiliateBasePropertyGroup(Affiliate $affiliate, BasePropertyGroup $basePropertyGroup): AffiliateBasePropertyGroup
    {
        $this->info('Create AffiliateBasePropertyGroup');

        $affiliateBasePropertyGroup = new AffiliateBasePropertyGroup();
        $affiliateBasePropertyGroup->affiliate_id = $affiliate->id;
        $affiliateBasePropertyGroup->base_property_group_id = $basePropertyGroup->id;
        $affiliateBasePropertyGroup->theme_id = optional($this->getTheme())->id;

        $commission = $this->getCommission();

        if ($commission) {
            $affiliateBasePropertyGroup->commissionable()->associate($commission);
        }

        $affiliateBasePropertyGroup->save();

        return $affiliateBasePropertyGroup;
    }

    /**
     * Get a base property
     *
     * @param \Stickee\Sync\Models\BasePropertyGroup $basePropertyGroup The base property group
     *
     * @return \Stickee\Sync\Models\BaseProperty
     */
    private function getBaseProperty(BasePropertyGroup $basePropertyGroup): BaseProperty
    {
        $baseProperties = BaseProperty::where('base_property_group_id', $basePropertyGroup->id)
            ->orderBy('name')
            ->get();
        $options = ['new' => '<Create new>'];

        $baseProperties->each(function ($item) use (&$options) {
            $options[$item->id] = $item->id . ': ' . $item->name . ' (' . $item->configuration_type . ')';
        })->all();

        if (count($options) === 1) {
            return $this->createBaseProperty($basePropertyGroup);
        }

        $selectedOption = $this->choice('Choose a base property or create a new one', $options, 'new');

        if ($selectedOption === 'new') {
            return $this->createBaseProperty($basePropertyGroup);
        }

        return $baseProperties->find($selectedOption);
    }

    /**
     * Create a base property
     *
     * @param \Stickee\Sync\Models\BasePropertyGroup $basePropertyGroup The base property group
     *
     * @return \Stickee\Sync\Models\BaseProperty
     */
    private function createBaseProperty(BasePropertyGroup $basePropertyGroup): BaseProperty
    {
        $this->info('Create BaseProperty');

        $baseProperty = new BaseProperty();
        $baseProperty->base_property_group_id = $basePropertyGroup->id;
        $baseProperty->name = $this->askRequired('Name?');

        $commission = $this->getCommission();

        if ($commission) {
            $baseProperty->commissionable()->associate($commission);
        }

        $baseProperty->configuration = $this->getPropertyConfiguration($basePropertyGroup);

        $baseProperty->save();

        return $baseProperty;
    }

    /**
     * Get a commission or null
     *
     * @param bool $required Require a commission
     *
     * @return ?\Stickee\Sync\Interfaces\CommissionInterface
     */
    private function getCommission(bool $required = false): ?CommissionInterface
    {
        if ($required) {
            return $this->createCommission();
        }

        $options = ['none' => '<None>', 'new' => '<Create new>'];

        $selectedOption = $this->choice('Commission?', $options, 'none');

        if ($selectedOption === 'new') {
            return $this->createCommission();
        }

        return null;
    }

    /**
     * Create a commission
     *
     * @return \Stickee\Sync\Interfaces\CommissionInterface
     */
    private function createCommission(): CommissionInterface
    {
        $this->info('Create Commission');

        $options = [
            FixedCommission::class,
            PercentageCommission::class,
        ];

        $selectedOption = $this->choice('Commission type?', $options);

        $class = explode('\\', $selectedOption);
        $function = 'create' . end($class);

        return $this->$function();
    }

    /**
     * Create a fixed commission
     *
     * @return \Stickee\Sync\Models\Commissions\FixedCommission
     */
    private function createFixedCommission(): FixedCommission
    {
        $this->info('Create FixedCommission');

        $commission = new FixedCommission();
        $commission->amount = $this->askRequired('Amount?', '0', function ($value) {
            return is_numeric($value) && ($value > 0);
        });
        $commission->save();

        return $commission;
    }

    /**
     * Create a percentage commission
     *
     * @return \Stickee\Sync\Models\Commissions\PercentageCommission
     */
    private function createPercentageCommission(): PercentageCommission
    {
        $this->info('Create PercentageCommission');

        $commission = new PercentageCommission();
        $commission->amount = $this->askRequired('Amount?', '0', function ($value) {
            return is_numeric($value) && ($value > 0) && ($value <= 100);
        });
        $commission->save();

        return $commission;
    }

    /**
     * Create a property and configuration
     *
     * @param \Stickee\Sync\Models\BaseProperty $baseProperty The base property
     * @param \Stickee\Sync\Models\AffiliateBasePropertyGroup $affiliateBasePropertyGroup The affiliate base property group
     *
     * @return \Stickee\Sync\Models\Property
     */
    private function createProperty(BaseProperty $baseProperty, AffiliateBasePropertyGroup $affiliateBasePropertyGroup): Property
    {
        $this->info('Create Property');

        $property = new Property();
        $property->affiliate_base_property_group_id = $affiliateBasePropertyGroup->id;
        $property->base_property_id = $baseProperty->id;
        $property->root = $this->askRequired('Site root URL (no http:// or trailing /)?', '', function ($value) {
            return filter_var('http://' . $value, FILTER_VALIDATE_URL)
                && !preg_match('_/$_', $value)
                && !Property::where('root', $value)->exists();
        });
        $property->theme_id = optional($this->getTheme())->id;

        $commission = $this->getCommission();

        if ($commission) {
            $property->commissionable()->associate($commission);
        }

        $property->configuration = $this->getPropertyConfiguration($baseProperty->basePropertyGroup, $baseProperty->configurable_type);
        $property->save();

        return $property;
    }

    /**
     * Get a property configuration
     *
     * @param \Stickee\Sync\Models\BasePropertyGroup $basePropertyGroup The base property group
     * @param ?string $type The property configuration type (class name)
     *
     * @return \Stickee\Sync\Interfaces\PropertyConfigurationInterface
     */
    private function getPropertyConfiguration(BasePropertyGroup $basePropertyGroup, ?string $type = null): PropertyConfigurationInterface
    {
        // Configuration is always required
        return $this->createPropertyConfiguration($basePropertyGroup, $type);
    }

    /**
     * Create a property configuration
     *
     * @param \Stickee\Sync\Models\BasePropertyGroup $basePropertyGroup The base property group
     * @param ?string $type The property configuration type (class name)
     *
     * @return \Stickee\Sync\Interfaces\PropertyConfigurationInterface
     */
    private function createPropertyConfiguration(BasePropertyGroup $basePropertyGroup, ?string $type = null): PropertyConfigurationInterface
    {
        $this->info('Create PropertyConfiguration');
        $isBase = $type === null;

        if (!$type) {
            $options = config('sync.property_configuration_types')[$basePropertyGroup->vertical->code];

            $type = $this->choice('Property configuration type?', $options);
        }

        switch ($type) {
            case BroadbandSite::class:
                return $this->createBroadbandSite($isBase);

            case MobilesSite::class:
                return $this->createMobilesSite($isBase);

            case PetInsuranceSite::class:
                return $this->createPetInsuranceSite($isBase);

            default:
                throw new Exception('Command not configured to create ' . $type);
        }
    }

    /**
     * Create a broadband site property configuration
     *
     * @param bool $isBase If this is for a base property (otherwise for a normal property)
     *
     * @return \Stickee\Sync\Models\PropertyConfigurations\BroadbandSite
     */
    private function createBroadbandSite(bool $isBase): BroadbandSite
    {
        $configuration = new BroadbandSite();
        // No options for now

        return $configuration;
    }

    /**
     * Create a mobiles site property configuration
     *
     * @param bool $isBase If this is for a base property (otherwise for a normal property)
     *
     * @return \Stickee\Sync\Models\PropertyConfigurations\MobilesSite
     */
    private function createMobilesSite(bool $isBase): MobilesSite
    {
        $configuration = new MobilesSite();
        // No options for now

        return $configuration;
    }

    /**
     * Create a pet insurance property configuration
     *
     * @param bool $isBase If this is for a base property (otherwise for a normal property)
     *
     * @return \Stickee\Sync\Models\PropertyConfigurations\PetInsuranceSite
     */
    private function createPetInsuranceSite(bool $isBase): PetInsuranceSite
    {
        $groupNames = [
            'msm' => 'msm',
            'whitelabels' => 'whitelabels',
            'go_compare' => 'go_compare',
        ];

        $configuration = new PetInsuranceSite();

        if ($isBase || $this->confirm('Override group name?')) {
            $configuration->groupName = $this->choice('Group Name?', $groupNames, 'whitelabels');
        }

        return $configuration;
    }

    /**
     * Ask a question and require an answer
     *
     * @param string $text The question text
     * @param ?string $default The default answer
     * @param mixed $validator The validator callable
     *
     * @return string
     */
    private function askRequired(string $text, ?string $default = null, $validator = null)
    {
        do {
            $value = $this->ask($text, $default);

            if ($value === null) {
                $this->error('Please enter a value');
            } elseif ($validator && !$validator($value)) {
                $this->error('Please enter a valid value');
                $value = null;
            }
        } while ($value === null);

        return $value;
    }
}
