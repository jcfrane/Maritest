<?php

namespace Database\Seeders;

use App\Models\Questionnaire;
use App\Models\QuestionnaireChoice;
use App\Models\QuestionnaireItem;
use App\Models\QuestionnairePage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class QuestionnaireSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@proact.com')->firstOrFail();
        $tenant = $user->tenants()->first();

        if (! $tenant instanceof Tenant) {
            throw new RuntimeException('QuestionnaireSeeder requires the admin user to belong to a tenant.');
        }

        $this->createSurveyQuestionnaire($tenant, $user);
        $this->createExamQuestionnaire($tenant, $user);
    }

    /**
     * A multi-page survey using per_page presentation mode.
     */
    private function createSurveyQuestionnaire(Tenant $tenant, User $user): void
    {
        $questionnaire = Questionnaire::factory()->for($tenant)->for($user)->create([
            'title' => 'Employee Satisfaction Survey',
            'description' => 'Annual survey to assess workplace satisfaction and gather feedback.',
            'status' => 'published',
            'settings' => [
                'time_limit' => null,
                'presentation_mode' => 'per_page',
                'items_per_step' => null,
                'allow_back_navigation' => true,
                'shuffle_pages' => false,
                'shuffle_items' => false,
            ],
        ]);

        // Page 1 — Work Environment
        $page1 = QuestionnairePage::factory()->for($questionnaire)->create([
            'title' => 'Work Environment',
            'description' => 'Questions about your day-to-day work environment.',
            'order' => 0,
        ]);

        $this->createInstruction($page1, 0, 'Please rate the following aspects of your work environment honestly.');

        $this->createRatingItem($page1, 1, 'How satisfied are you with your physical workspace?');
        $this->createRatingItem($page1, 2, 'How would you rate the tools and equipment provided?');
        $this->createSingleChoiceItem($page1, 3, 'Do you feel the office temperature is comfortable?', [
            'Always', 'Most of the time', 'Sometimes', 'Rarely', 'Never',
        ]);

        // Page 2 — Team & Communication
        $page2 = QuestionnairePage::factory()->for($questionnaire)->create([
            'title' => 'Team & Communication',
            'description' => 'Tell us about collaboration and communication within your team.',
            'order' => 1,
        ]);

        $this->createRatingItem($page2, 0, 'How well does your team collaborate on projects?');
        $this->createMultipleChoiceItem($page2, 1, 'Which communication tools do you use regularly?', [
            'Email', 'Slack', 'Microsoft Teams', 'Video calls', 'In-person meetings',
        ]);
        $this->createLongTextItem($page2, 2, 'What could improve communication in your team?');

        // Page 3 — Growth & Feedback
        $page3 = QuestionnairePage::factory()->for($questionnaire)->create([
            'title' => 'Growth & Feedback',
            'description' => 'Share your thoughts on career development and recognition.',
            'order' => 2,
        ]);

        $this->createSingleChoiceItem($page3, 0, 'How often do you receive constructive feedback from your manager?', [
            'Weekly', 'Monthly', 'Quarterly', 'Rarely', 'Never',
        ]);
        $this->createRatingItem($page3, 1, 'How satisfied are you with career growth opportunities?');
        $this->createLongTextItem($page3, 2, 'Any additional comments or suggestions?');
    }

    /**
     * A multi-page exam using per_item presentation mode (1 item at a time).
     */
    private function createExamQuestionnaire(Tenant $tenant, User $user): void
    {
        $questionnaire = Questionnaire::factory()->for($tenant)->for($user)->create([
            'title' => 'General Knowledge Exam',
            'description' => 'A timed exam covering general knowledge topics. One question at a time.',
            'status' => 'published',
            'settings' => [
                'time_limit' => 30,
                'presentation_mode' => 'per_item',
                'items_per_step' => 1,
                'allow_back_navigation' => false,
                'shuffle_pages' => false,
                'shuffle_items' => true,
            ],
        ]);

        // Page 1 — Science
        $page1 = QuestionnairePage::factory()->for($questionnaire)->create([
            'title' => 'Science',
            'description' => 'Basic science questions.',
            'order' => 0,
        ]);

        $this->createInstruction($page1, 0, 'Answer each question carefully. You cannot go back once you proceed.');

        $this->createSingleChoiceItem($page1, 1, 'What is the chemical symbol for water?', [
            'H2O', 'CO2', 'NaCl', 'O2',
        ], 0);

        $this->createSingleChoiceItem($page1, 2, 'What planet is known as the Red Planet?', [
            'Venus', 'Mars', 'Jupiter', 'Saturn',
        ], 1);

        $this->createSingleChoiceItem($page1, 3, 'What gas do plants absorb from the atmosphere?', [
            'Oxygen', 'Nitrogen', 'Carbon dioxide', 'Hydrogen',
        ], 2);

        // Page 2 — History
        $page2 = QuestionnairePage::factory()->for($questionnaire)->create([
            'title' => 'History',
            'description' => 'World history questions.',
            'order' => 1,
        ]);

        $this->createSingleChoiceItem($page2, 0, 'In which year did World War II end?', [
            '1943', '1944', '1945', '1946',
        ], 2);

        $this->createSingleChoiceItem($page2, 1, 'Who was the first President of the United States?', [
            'Thomas Jefferson', 'George Washington', 'Abraham Lincoln', 'John Adams',
        ], 1);

        $this->createSingleChoiceItem($page2, 2, 'The Great Wall of China was primarily built to protect against invasions from which direction?', [
            'South', 'East', 'West', 'North',
        ], 3);

        // Page 3 — Math
        $page3 = QuestionnairePage::factory()->for($questionnaire)->create([
            'title' => 'Mathematics',
            'description' => 'Basic math questions.',
            'order' => 2,
        ]);

        $this->createSingleChoiceItem($page3, 0, 'What is the square root of 144?', [
            '10', '11', '12', '14',
        ], 2);

        $this->createSingleChoiceItem($page3, 1, 'What is 15% of 200?', [
            '15', '25', '30', '35',
        ], 2);

        $this->createShortTextItem($page3, 2, 'What is the value of pi rounded to two decimal places?');
    }

    private function createInstruction(QuestionnairePage $page, int $order, string $content): QuestionnaireItem
    {
        return QuestionnaireItem::factory()
            ->for($page, 'page')
            ->instruction()
            ->create([
                'content' => "<p>{$content}</p>",
                'order' => $order,
            ]);
    }

    private function createRatingItem(QuestionnairePage $page, int $order, string $content): QuestionnaireItem
    {
        return QuestionnaireItem::factory()
            ->for($page, 'page')
            ->create([
                'type' => 'rating',
                'content' => "<p>{$content}</p>",
                'required' => true,
                'order' => $order,
                'properties' => ['max_stars' => 5],
            ]);
    }

    private function createLongTextItem(QuestionnairePage $page, int $order, string $content): QuestionnaireItem
    {
        return QuestionnaireItem::factory()
            ->for($page, 'page')
            ->create([
                'type' => 'long_text',
                'content' => "<p>{$content}</p>",
                'required' => false,
                'order' => $order,
            ]);
    }

    private function createShortTextItem(QuestionnairePage $page, int $order, string $content): QuestionnaireItem
    {
        return QuestionnaireItem::factory()
            ->for($page, 'page')
            ->create([
                'type' => 'short_text',
                'content' => "<p>{$content}</p>",
                'required' => true,
                'order' => $order,
            ]);
    }

    /**
     * @param  list<string>  $options
     */
    private function createSingleChoiceItem(
        QuestionnairePage $page,
        int $order,
        string $content,
        array $options,
        ?int $correctIndex = null,
    ): QuestionnaireItem {
        $item = QuestionnaireItem::factory()
            ->for($page, 'page')
            ->singleChoice()
            ->create([
                'content' => "<p>{$content}</p>",
                'required' => true,
                'order' => $order,
                'properties' => ['label_type' => 'alphabetical'],
            ]);

        foreach ($options as $i => $option) {
            QuestionnaireChoice::factory()->for($item, 'item')->create([
                'content' => $option,
                'is_correct' => $correctIndex === $i,
                'order' => $i,
            ]);
        }

        return $item;
    }

    /**
     * @param  list<string>  $options
     */
    private function createMultipleChoiceItem(
        QuestionnairePage $page,
        int $order,
        string $content,
        array $options,
    ): QuestionnaireItem {
        $item = QuestionnaireItem::factory()
            ->for($page, 'page')
            ->multipleChoice()
            ->create([
                'content' => "<p>{$content}</p>",
                'required' => true,
                'order' => $order,
                'properties' => ['label_type' => 'alphabetical'],
            ]);

        foreach ($options as $i => $option) {
            QuestionnaireChoice::factory()->for($item, 'item')->create([
                'content' => $option,
                'is_correct' => false,
                'order' => $i,
            ]);
        }

        return $item;
    }
}
