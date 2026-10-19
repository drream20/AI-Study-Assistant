# AI Study Assistant — Complete Project Plan

## 1. Project Overview

Build an Android APK that lets users upload PDFs or images of lessons, generate AI-powered questionnaires and flashcards, edit their content, and study offline.

The app will use Vue 3 and Capacitor for the mobile interface, Laravel and PHP for the backend, an AI API for generating learning materials, and SQLite for local storage.

**Core principle:** Generate online once, save locally, and study anytime.

---

## 2. UI Design System

### Design Direction

Use the provided AI Study Assistant mockup as the primary visual reference.

The interface should feel:
- Modern, clean, and friendly for students.
- Easy to understand without a tutorial.
- Consistent across all screens.
- Comfortable to use on small Android phones.
- Focused on readable content rather than unnecessary decoration.
- Designed for light mode in the first version.

### Color Palette

| Purpose | Color | Hex |
|---|---|---|
| Primary actions and active navigation | Bright blue | `#2563EB` |
| Dark headings and main text | Navy | `#14233B` |
| Main screen background | Pale blue-gray | `#F4F7FC` |
| Cards and input backgrounds | White | `#FFFFFF` |
| Secondary text | Slate gray | `#64748B` |
| Borders and dividers | Light gray-blue | `#E2E8F0` |
| Success and “Know It” | Green | `#10B981` |
| Review reminders | Pink / rose | `#F43F6E` |
| Highlights and favorites | Amber | `#F59E0B` |
| Optional category accent | Purple | `#8B5CF6` |

**Color usage rules**
- Use bright blue for primary buttons, links, progress indicators, and selected navigation.
- Use navy for headings and important information.
- Use pale blue-gray for the overall background and white for content cards.
- Use green for successful actions and remembered flashcards.
- Use rose for “Review Again” and destructive-action warnings where appropriate.
- Use amber sparingly for favorites and important highlights.
- Do not rely on color alone to communicate a status; include labels or icons.

### Typography

Use **Inter** as the primary app font, with Android's Roboto or a system sans-serif fallback.

| Element | Suggested size | Weight |
|---|---:|---|
| Main screen title | 24–28 sp | Bold |
| Section headings | 18–20 sp | Semibold |
| Card titles | 15–17 sp | Semibold |
| Body and question text | 14–16 sp | Regular |
| Supporting text | 12–14 sp | Regular |
| Buttons and navigation labels | 12–14 sp | Medium |
| Small metadata | 11–12 sp | Regular |

Use Android-independent CSS sizes appropriate for mobile, and test all text at larger system font settings. Keep question text especially readable, with comfortable line height.

### Layout and Spacing

- Follow a mobile-first layout designed for Android phone screens.
- Use a consistent spacing scale based on 4-pixel increments.
- Use approximately 16–20 px of horizontal screen padding.
- Use 12–16 px internal card padding.
- Use 12–16 px gaps between related cards.
- Use rounded cards with approximately 14–20 px corner radii.
- Use pill-shaped filters, badges, and segmented controls where appropriate.
- Give primary actions clear visual priority.
- Avoid excessively dense lists, crowded screens, and horizontal scrolling.
- Account for Android status bars, navigation areas, and safe-area insets.
- Ensure touch targets are comfortably large, ideally around 44–48 px or more.

### Navigation Structure

Use a consistent bottom navigation bar with five main destinations:

1. **Home** — upload materials and view recent activity.
2. **Library** — manage saved study sets.
3. **Flashcards** — access card decks and review sessions.
4. **Quizzes** — take practice tests.
5. **More** — settings, backup, import, and app information.

Use blue for the selected destination and neutral gray for inactive destinations. Each item should have a clear icon and short text label.

### Reusable UI Components

Create reusable components for:
- Primary, secondary, and destructive buttons.
- Study-set cards.
- Search fields and filter chips.
- Question and answer forms.
- Flashcards and review buttons.
- Progress indicators and statistics.
- Empty states, loading indicators, and error messages.
- Confirmation dialogs and success notifications.
- Bottom navigation and section headers.

This keeps the design consistent and makes future changes easier.

---

## 3. Main App Screens

### A. Home Dashboard
- App logo and welcome heading.
- Prominent “Upload PDF / Image” action.
- Quick access to the Study Library, Flashcards, and Quizzes.
- Recently opened study sets.
- Generation and saving status indicators.

### B. Study Library
- Search by title or topic.
- Organize study sets by subject, school year, topic, or custom folder.
- Filter by All, Favorites, and Recently Opened.
- Sort by newest, oldest, alphabetical order, or recently studied.
- Display question count, flashcard count, and last studied date.
- Support opening, editing, duplicating, exporting, and deleting study sets.

### C. Study Set Details
- Study-set title and subject.
- Overview, Questions, and Flashcards sections.
- Original document information when available.
- Study, edit, duplicate, export, and delete actions.
- Clear indication that the material is saved on the device.

### D. Flashcards
- Display one card at a time.
- Tap to reveal the answer.
- Buttons for “Know It” and “Review Again.”
- Show the current card number and progress.
- Support shuffling and offline review.

### E. Quiz Mode
- Support multiple-choice, identification, and enumeration questions.
- Show question progress and an optional timer.
- Support shuffled question order.
- Score answers automatically when applicable.
- Display results, missed questions, and explanations.
- Save quiz history locally.

### F. Edit Question
- Edit question wording and question type.
- Add, edit, reorder, or remove multiple-choice options.
- Select the correct answer.
- Edit acceptable answers for identification and enumeration.
- Add or update explanations.
- Save edits automatically.

### G. Flashcard Progress
- Display learned, new, and review-needed card totals.
- Track review history and overall progress.
- Show cards due for review.
- Schedule future reviews using spaced repetition.

### H. Settings / More
- Manage profile preferences if accounts are enabled.
- Import and export study sets.
- Back up and restore saved data.
- Manage app preferences and storage.
- Display help, privacy information, and app version.

---

## 4. Upload and Question Generation

Users should be able to upload an image, a text-based PDF, or a scanned PDF.

Before generation, allow them to customize:
- Number of questions.
- Question types.
- Difficulty level.
- Output language.
- Whether to generate flashcards.
- Optional preferences such as explanations or page references.

The backend extracts the source text and requests structured output from the AI service.

Generated content must be validated before it is shown as complete. The user must be able to review and edit every generated question and answer.

---

## 5. Automatic Local Saving

Every successfully generated study set must be saved automatically to the phone.

Save:
- Study-set metadata.
- Questions, question types, and answer options.
- Correct answers and explanations.
- Flashcards and their review status.
- Quiz attempts and scores.
- User edits and study progress.
- Original PDF or image locally when the user chooses to keep it.

Use SQLite through the Capacitor SQLite plugin. Perform reliable database writes so a failed save does not leave a misleadingly complete study set.

Show a clear “Saved on device” status. Do not require a separate Save button for the normal generation workflow.

Users must be able to edit and delete individual questions, flashcards, or entire study sets. Provide confirmation for deleting a complete set and consider a Recently Deleted area for recovery.

---

## 6. Smart Flashcards and Spaced Repetition

- Let users reveal each answer.
- Record “Know It” and “Review Again” responses.
- Schedule difficult cards for earlier review.
- Increase the interval after successful recall.
- Store review history and next-review dates locally.
- Keep the review system functional without internet access.
- Show progress and the cards due for review.

The scheduling algorithm should be adjustable later without requiring users to recreate their flashcards.

---

## 7. Technology Stack

| Component | Technology |
|---|---|
| Code editor | VS Code |
| Mobile interface | Vue 3 |
| Android packaging | Capacitor |
| Backend | Laravel + PHP |
| Local database | SQLite through Capacitor SQLite |
| Optional cloud database | MySQL or MariaDB |
| PDF text extraction | PHP PDF parser |
| Scanned document OCR | OCR processing for PDF pages and images |
| AI generation | AI API called securely by Laravel |
| Android build tools | Android SDK and Gradle |

Do not store AI API keys inside the APK. Store them in the Laravel server environment and make AI requests from the backend.

---

## 8. Offline-First Requirements

After content has been generated and saved, users must be able to:
- Open saved study sets.
- Read retained source PDFs.
- Edit and delete questions and flashcards.
- Take quizzes.
- Reveal flashcard answers.
- Save progress and quiz results.
- Use spaced repetition.
- Search the local study library.

These features must work in airplane mode. Internet is required only for AI generation and optional cloud operations.

Uninstalling the app or clearing its storage may remove local data, so export and backup features should be added to reduce the risk of losing study materials.

---

## 9. Development Roadmap

1. Set up the Laravel backend and Vue + Capacitor mobile project.
2. Implement the UI design system and reusable components.
3. Create the local SQLite database and study library.
4. Add automatic saving, editing, deleting, searching, and favorites.
5. Build flashcard and quiz modes that work offline.
6. Add study progress and spaced repetition.
7. Implement PDF/image upload, document extraction, and OCR.
8. Integrate the AI API through Laravel.
9. Add export, import, and optional cloud backup.
10. Test offline behavior, small-screen layouts, accessibility, and APK installation.

---

## 10. Definition of a Successful First Version

The first version is successful when a user can upload a lesson, generate customized questions and flashcards, have everything saved automatically on the phone, edit or delete any item, close and reopen the app, and continue studying without internet access.

**Design principle:** Clean blue-and-white visuals, readable typography, consistent rounded components, simple navigation, and offline study as a first-class feature.