<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The assessment hub (and the home-page assessments section) copy is
     * configurable so the platform can promote an ASCPi challenge, a hosted
     * course, or any other assessment type without touching Blade templates.
     */
    public function up(): void
    {
        $settings = [
            ['hub.eyebrow', 'ASCPi Challenge', 'string', 'Eyebrow / kicker text above the section heading.'],
            ['hub.heading', 'Assessments', 'string', 'Headline of the assessments section on the home page.'],
            ['hub.subtitle', 'Timed, proctored challenge exams built on the ASCP Board of Certification content outline. One official attempt per challenge - practice runs are available once your attempt is in.', 'text', 'Description shown in the assessment hub hero.'],
            ['hub.home_subtitle', 'Timed challenge exams with a live leaderboard, official results, and verifiable certificates.', 'text', 'Description shown in the home-page assessments section.'],
            ['hub.card_cta', 'Take Challenge', 'string', 'Primary button label on each assessment card in the hub.'],
            ['hub.open_now_label', 'Open now', 'string', 'Badge label on the flagship assessment card.'],
            ['hub.primary_cta', 'Take the challenge', 'string', 'Call to action on the flagship assessment card.'],
            ['hub.card_meta_template', '{questions} questions · {minutes} minutes · {attempt}.', 'string', 'Meta line on the flagship card. Placeholders: {questions}, {minutes}, {attempt}.'],
            ['hub.next_preparing_title', 'The next challenge is being prepared', 'string', 'Headline when no assessment is currently open.'],
            ['hub.next_preparing_text', 'Browse past assessments, leaderboards, and your certificates while you wait.', 'text', 'Supporting text when no assessment is currently open.'],
            ['hub.visit_hub_label', 'Visit the hub', 'string', 'Call to action on the "next challenge" card.'],
            ['hub.how_works_title', 'How it works', 'string', 'Heading of the side panel on the assessment hub.'],
            ['hub.how_works_items', "Questions are randomized per participant - order and options included.\nOne official attempt per challenge. The server clock runs the timer.\nScores stay hidden until results are officially released.\nFinish strong and earn a verifiable certificate with your rank.", 'text', 'Bullet points for "How it works". One item per line.'],
            ['hub.practice_title', 'Practice makes perfect', 'string', 'Heading of the practice side panel on the assessment hub.'],
            ['hub.practice_text', 'Once your official attempt is submitted, practice runs let you replay similar questions - unranked, untethered from the leaderboard.', 'text', 'Body of the practice side panel on the assessment hub.'],
            ['hub.practice_link_label', 'Check your results', 'string', 'Link label in the practice side panel.'],
            ['hub.leaderboard_label', 'Leaderboard', 'string', 'Home-page leaderboard card title.'],
            ['hub.leaderboard_text', 'See who is leading this month\'s challenge.', 'text', 'Home-page leaderboard card description.'],
            ['hub.leaderboard_link', 'View standings', 'string', 'Home-page leaderboard card link.'],
            ['hub.practice_runs_label', 'Practice Runs', 'string', 'Home-page practice runs card title.'],
            ['hub.practice_runs_text', 'Replay challenges unranked after your official attempt.', 'text', 'Home-page practice runs card description.'],
            ['hub.practice_runs_link', 'Open the hub', 'string', 'Home-page practice runs card link.'],
            ['hub.next_coming_label', 'Next challenge coming soon.', 'string', 'Note shown next to closed assessments in the hub.'],
        ];

        foreach ($settings as [$key, $value, $type, $description]) {
            $label = ucwords(str_replace('_', ' ', (string) \Illuminate\Support\Str::after($key, 'hub.')));

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => $type, 'group' => 'hub', 'label' => $label, 'description' => $description]
            );
        }

        \Illuminate\Support\Facades\Cache::forget('settings.all');
    }

    public function down(): void
    {
        Setting::where('group', 'hub')->delete();

        \Illuminate\Support\Facades\Cache::forget('settings.all');
    }
};