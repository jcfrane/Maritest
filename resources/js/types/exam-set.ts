export interface ExamSetQuestionnaire {
    id: number;
    title: string;
    description: string | null;
    status: 'draft' | 'published';
    position?: number | null;
}

export interface ExamSet {
    id?: number;
    title: string;
    description: string | null;
    status: 'draft' | 'published';
    questionnaires: ExamSetQuestionnaire[];
    questionnaire_count?: number;
    created_at?: string;
    updated_at?: string;
}
