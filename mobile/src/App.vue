<script setup lang="ts">
import { computed, ref } from 'vue'
import {
  Activity, ArrowLeft, ArrowRight, Bell, BookOpen, Bookmark, CalendarDays,
  Check, CheckCircle2, ChevronRight, Clock3, Cloud, FileText, Filter,
  FolderOpen, GraduationCap, House, Library, ListChecks, MoreHorizontal,
  Pencil, Play, Plus, Search, Settings, Shuffle, Sparkles, Star, Timer,
  Trash2, Trophy, Upload, X, Zap,
} from 'lucide-vue-next'

type Tab = 'home' | 'library' | 'flashcards' | 'quizzes' | 'more'
type Screen = Tab | 'details' | 'edit-set' | 'edit-question' | 'study' | 'quiz' | 'results' | 'progress' | 'activity' | 'settings'
type CardStatus = 'known' | 'review' | 'new'

interface Flashcard {
  front: string
  back: string
  status: CardStatus
}

interface Question {
  prompt: string
  options: string[]
  correct: number
  explanation: string
}

interface StudySet {
  id: string
  title: string
  subject: string
  color: string
  updated: string
  favorite: boolean
  questions: Question[]
  cards: Flashcard[]
}

interface ActivityEntry {
  title: string
  detail: string
  time: string
  kind: 'edit' | 'study' | 'quiz' | 'create' | 'delete'
}

const screen = ref<Screen>('home')
const activeTab = ref<Tab>('home')
const selectedSetId = ref('biology')
const search = ref('')
const filter = ref<'All' | 'Favorites' | 'Recently Opened'>('All')
const detailTab = ref<'Overview' | 'Questions' | 'Flashcards'>('Overview')
const cardIndex = ref(0)
const answerRevealed = ref(false)
const quizIndex = ref(0)
const quizScore = ref(0)
const chosenAnswer = ref<number | null>(null)
const editQuestionIndex = ref<number | null>(null)
const questionDraft = ref<Question>({ prompt: '', options: ['', '', '', ''], correct: 0, explanation: '' })
const setDraft = ref({ title: '', subject: '' })
const toast = ref('')
const fileInput = ref<HTMLInputElement | null>(null)
const showDelete = ref(false)
const remindersEnabled = ref(true)
const shuffleEnabled = ref(false)

const sets = ref<StudySet[]>([
  {
    id: 'biology', title: 'Biology - Chapter 4', subject: 'Biology', color: 'mint', updated: 'Today, 10:24 AM', favorite: true,
    questions: [
      { prompt: 'Which organelle is responsible for cellular respiration?', options: ['Chloroplast', 'Mitochondrion', 'Ribosome', 'Golgi apparatus'], correct: 1, explanation: 'Mitochondria convert energy from nutrients into ATP through cellular respiration.' },
      { prompt: 'What is the main function of chloroplasts in plant cells?', options: ['Store water', 'Carry out photosynthesis', 'Build proteins', 'Control cell division'], correct: 1, explanation: 'Chloroplasts use light energy to make sugars through photosynthesis.' },
      { prompt: 'Which structure controls what enters and leaves a cell?', options: ['Cell membrane', 'Nucleus', 'Ribosome', 'Vacuole'], correct: 0, explanation: 'The cell membrane is selectively permeable and regulates movement into and out of the cell.' },
    ],
    cards: [
      { front: 'What is the main function of chloroplasts in plant cells?', back: 'They capture light energy and use it to produce sugars through photosynthesis.', status: 'review' },
      { front: 'Where does cellular respiration take place?', back: 'Primarily in the mitochondria.', status: 'known' },
      { front: 'What does selectively permeable mean?', back: 'A membrane lets some substances pass through while blocking others.', status: 'new' },
    ],
  },
  {
    id: 'computer', title: 'Computer Science Basics', subject: 'Computer Science', color: 'blue', updated: 'Yesterday, 4:32 PM', favorite: true,
    questions: [
      { prompt: 'What does CPU stand for?', options: ['Central Processing Unit', 'Computer Personal Utility', 'Core Program Upload', 'Central Power Unit'], correct: 0, explanation: 'The CPU executes instructions and coordinates many operations in a computer.' },
      { prompt: 'Which number system do computers use at the hardware level?', options: ['Decimal', 'Binary', 'Roman', 'Hexadecimal only'], correct: 1, explanation: 'Digital circuits represent information using two states, commonly represented as 0 and 1.' },
    ],
    cards: [
      { front: 'What is an algorithm?', back: 'A finite, ordered set of steps for solving a problem or completing a task.', status: 'review' },
      { front: 'What does RAM store?', back: 'Data and instructions the computer is actively using.', status: 'new' },
    ],
  },
  {
    id: 'history', title: 'World History - Ancient Rome', subject: 'History', color: 'amber', updated: 'Apr 18, 2025', favorite: false,
    questions: [{ prompt: 'Which body was central to political debate in the Roman Republic?', options: ['The Senate', 'The Imperial Court', 'The Ephors', 'The Areopagus'], correct: 0, explanation: 'The Senate was a powerful advisory body that shaped policy and public life.' }],
    cards: [{ front: 'What were the Twelve Tables?', back: 'The earliest written collection of Roman laws, published around 450 BCE.', status: 'new' }],
  },
  {
    id: 'algebra', title: 'Mathematics - Algebra', subject: 'Mathematics', color: 'violet', updated: 'Apr 16, 2025', favorite: false,
    questions: [{ prompt: 'Solve for x: 3x + 5 = 20', options: ['3', '5', '8', '15'], correct: 1, explanation: 'Subtract 5 from both sides, then divide by 3: x = 5.' }],
    cards: [{ front: 'What is the distributive property?', back: 'a(b + c) = ab + ac.', status: 'known' }],
  },
])

const activity = ref<ActivityEntry[]>([
  { title: 'Biology - Chapter 4', detail: '12 questions · 8 flashcards', time: 'Today, 10:24 AM', kind: 'study' },
  { title: 'Computer Science Basics', detail: '15 questions · 12 flashcards', time: 'Yesterday, 4:32 PM', kind: 'edit' },
  { title: 'World History - Ancient Rome', detail: '10 questions · 6 flashcards', time: 'Apr 18, 2025', kind: 'create' },
])

const navItems: { id: Tab; label: string; icon: typeof House }[] = [
  { id: 'home', label: 'Home', icon: House },
  { id: 'library', label: 'Library', icon: Library },
  { id: 'flashcards', label: 'Flashcards', icon: BookOpen },
  { id: 'quizzes', label: 'Quizzes', icon: ListChecks },
  { id: 'more', label: 'More', icon: MoreHorizontal },
]

const currentSet = computed(() => sets.value.find((item) => item.id === selectedSetId.value) ?? null)
const currentCard = computed(() => currentSet.value?.cards[cardIndex.value] ?? null)
const currentQuestion = computed(() => currentSet.value?.questions[quizIndex.value] ?? null)
const filteredSets = computed(() => sets.value.filter((item) => {
  const matchesSearch = `${item.title} ${item.subject}`.toLowerCase().includes(search.value.toLowerCase().trim())
  const matchesFilter = filter.value === 'All'
    || (filter.value === 'Favorites' && item.favorite)
    || (filter.value === 'Recently Opened' && ['Today, 10:24 AM', 'Yesterday, 4:32 PM', 'Today, just now'].includes(item.updated))
  return matchesSearch && matchesFilter
}))
const totalCards = computed(() => sets.value.reduce((sum, item) => sum + item.cards.length, 0))
const knownCards = computed(() => sets.value.reduce((sum, item) => sum + item.cards.filter((card) => card.status === 'known').length, 0))
const dueCards = computed(() => sets.value.reduce((sum, item) => sum + item.cards.filter((card) => card.status === 'review').length, 0))
const showBottomNav = computed(() => navItems.some((item) => item.id === screen.value))

function notify(message: string) {
  toast.value = message
  window.setTimeout(() => { if (toast.value === message) toast.value = '' }, 2600)
}

function logActivity(title: string, detail: string, kind: ActivityEntry['kind']) {
  activity.value.unshift({ title, detail, time: 'Just now', kind })
}

function navigate(tab: Tab) {
  activeTab.value = tab
  screen.value = tab
}

function openSet(id: string) {
  selectedSetId.value = id
  detailTab.value = 'Overview'
  screen.value = 'details'
}

function createSet() {
  const id = `set-${Date.now()}`
  const created: StudySet = { id, title: 'New study set', subject: 'General', color: 'blue', updated: 'Today, just now', favorite: false, questions: [], cards: [] }
  sets.value.unshift(created)
  selectedSetId.value = id
  setDraft.value = { title: created.title, subject: created.subject }
  screen.value = 'edit-set'
}

function editSet() {
  if (!currentSet.value) return
  setDraft.value = { title: currentSet.value.title, subject: currentSet.value.subject }
  screen.value = 'edit-set'
}

function saveSet() {
  if (!currentSet.value || !setDraft.value.title.trim()) return notify('Add a title before saving.')
  currentSet.value.title = setDraft.value.title.trim()
  currentSet.value.subject = setDraft.value.subject.trim() || 'General'
  currentSet.value.updated = 'Today, just now'
  logActivity('Study set edited', currentSet.value.title, 'edit')
  screen.value = 'details'
  notify('Updated in this preview. Local saving is not connected yet.')
}

function toggleFavorite(item: StudySet) {
  item.favorite = !item.favorite
  logActivity(item.favorite ? 'Added to Favorites' : 'Removed from Favorites', item.title, 'edit')
}

function deleteSet() {
  if (!currentSet.value) return
  const title = currentSet.value.title
  sets.value = sets.value.filter((item) => item.id !== selectedSetId.value)
  logActivity('Study set deleted', title, 'delete')
  screen.value = 'library'
  activeTab.value = 'library'
  showDelete.value = false
  notify('Removed from this preview.')
}

function duplicateSet() {
  if (!currentSet.value) return
  const clone = structuredClone(currentSet.value)
  clone.id = `set-${Date.now()}`
  clone.title = `${clone.title} (copy)`
  clone.favorite = false
  sets.value.unshift(clone)
  selectedSetId.value = clone.id
  logActivity('Study set duplicated', clone.title, 'create')
  notify('Copy added to this preview.')
}

function startStudy(id = selectedSetId.value) {
  selectedSetId.value = id
  cardIndex.value = 0
  answerRevealed.value = false
  activeTab.value = 'flashcards'
  screen.value = 'study'
}

function gradeCard(status: CardStatus) {
  if (!currentCard.value || !currentSet.value) return
  currentCard.value.status = status
  if (cardIndex.value < currentSet.value.cards.length - 1) {
    cardIndex.value += 1
    answerRevealed.value = false
    return
  }
  logActivity('Flashcards reviewed', `${currentSet.value.title} · ${currentSet.value.cards.length} cards`, 'study')
  notify('Review session complete.')
  screen.value = 'progress'
}

function shuffleDeck() {
  if (!currentSet.value) return
  const deck = currentSet.value.cards
  for (let index = deck.length - 1; index > 0; index -= 1) {
    const other = Math.floor(Math.random() * (index + 1))
    ;[deck[index], deck[other]] = [deck[other], deck[index]]
  }
  cardIndex.value = 0
  answerRevealed.value = false
  notify('Deck shuffled.')
}

function startQuiz(id = selectedSetId.value) {
  selectedSetId.value = id
  quizIndex.value = 0
  quizScore.value = 0
  chosenAnswer.value = null
  activeTab.value = 'quizzes'
  screen.value = 'quiz'
}

function submitAnswer() {
  if (!currentQuestion.value || chosenAnswer.value === null || !currentSet.value) return
  if (chosenAnswer.value === currentQuestion.value.correct) quizScore.value += 1
  if (quizIndex.value < currentSet.value.questions.length - 1) {
    quizIndex.value += 1
    chosenAnswer.value = null
    return
  }
  logActivity('Quiz completed', `${currentSet.value.title} · ${quizScore.value}/${currentSet.value.questions.length}`, 'quiz')
  screen.value = 'results'
}

function editQuestion(index: number | null = null) {
  if (!currentSet.value) return
  const existing = index === null ? null : currentSet.value.questions[index]
  questionIndex.value = index
  questionDraft.value = existing ? { ...existing, options: [...existing.options] } : { prompt: '', options: ['', '', '', ''], correct: 0, explanation: '' }
  screen.value = 'edit-question'
}

function saveQuestion() {
  if (!currentSet.value || !questionDraft.value.prompt.trim()) return notify('Add a question before saving.')
  const question = { ...questionDraft.value, prompt: questionDraft.value.prompt.trim(), options: questionDraft.value.options.map((option) => option.trim()).filter(Boolean) }
  if (questionIndex.value === null) currentSet.value.questions.push(question)
  else currentSet.value.questions.splice(questionIndex.value, 1, question)
  logActivity(questionIndex.value === null ? 'Question added' : 'Question edited', currentSet.value.title, 'edit')
  screen.value = 'details'
  detailTab.value = 'Questions'
  notify('Question updated in this preview.')
}

function pickUpload() {
  fileInput.value?.click()
}

function fileSelected(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (file) notify(`${file.name} selected. Upload and AI generation are not connected yet.`)
  input.value = ''
}
</script>

<template>
  <div class="app-frame">
    <header class="topbar">
      <button class="brand" type="button" aria-label="Studyday Home" @click="navigate('home')"><span class="brand-mark"><Sparkles :size="17" /></span><span>Studyday</span></button>
      <div class="topbar-actions"><span class="preview-label">UI PREVIEW</span><button class="icon-button notification-button" type="button" aria-label="Notifications" @click="notify('You are all caught up.')"><Bell :size="18" /><i></i></button><button class="avatar" type="button" aria-label="Settings" @click="screen = 'settings'">A</button></div>
    </header>

    <main class="app-main">
      <section v-if="screen === 'home'" class="page home-page">
        <div class="welcome-row"><div><p class="eyebrow">MONDAY, OCTOBER 19</p><h1>Good morning, Alex</h1><p class="muted">Learn smarter. Anywhere.</p></div><span class="streak"><Zap :size="14" fill="currentColor" /> 4 day streak</span></div>
        <section class="upload-panel"><div class="upload-copy"><span class="panel-kicker"><Sparkles :size="13" /> YOUR STUDY SPACE</span><h2>Make your next lesson stick.</h2><p>Turn class notes into questions and flashcards.</p></div><div class="upload-art" aria-hidden="true"><span class="art-page page-back"></span><span class="art-page page-front"><FileText :size="25" /><i></i><i></i><i></i></span><span class="art-spark"><Sparkles :size="17" /></span></div><div class="upload-actions"><button class="button button-white" type="button" @click="pickUpload"><Upload :size="16" /> Upload PDF / Image</button><button class="button button-glass" type="button" @click="createSet"><Plus :size="17" /> New study set</button></div><p class="upload-foot"><FileText :size="13" /> PDF or image <span>·</span> AI generation will connect next</p></section>
        <div class="quick-grid"><button class="quick-link library-link" type="button" @click="navigate('library')"><span class="quick-icon"><FolderOpen :size="21" /></span><span><strong>My Study Library</strong><small>View your saved study sets</small></span><ChevronRight :size="17" /></button><button class="quick-link" type="button" @click="navigate('flashcards')"><span class="quick-icon teal-icon"><BookOpen :size="20" /></span><span><strong>Flashcards</strong><small>Review and learn</small></span></button><button class="quick-link" type="button" @click="navigate('quizzes')"><span class="quick-icon coral-icon"><ListChecks :size="20" /></span><span><strong>Quizzes</strong><small>Test your knowledge</small></span></button></div>
        <section class="section-block"><div class="section-heading"><div><p class="eyebrow">PICK UP WHERE YOU LEFT OFF</p><h2>Recent study sets</h2></div><button class="text-button" type="button" @click="navigate('library')">View all <ArrowRight :size="14" /></button></div><article v-for="item in sets.slice(0, 3)" :key="item.id" class="recent-card"><button class="recent-open" type="button" @click="openSet(item.id)"><span class="subject-tile" :class="`tile-${item.color}`"><BookOpen :size="19" /></span><span class="recent-copy"><strong>{{ item.title }}</strong><small>{{ item.questions.length }} questions <i></i> {{ item.cards.length }} flashcards</small><small class="recent-date">{{ item.updated }}</small></span></button><button class="favorite-button" type="button" :aria-label="item.favorite ? 'Remove favorite' : 'Add favorite'" @click="toggleFavorite(item)"><Star :size="16" :fill="item.favorite ? 'currentColor' : 'none'" /></button></article></section>
        <section class="section-block"><div class="section-heading"><div><p class="eyebrow">YOUR LEARNING JOURNEY</p><h2>Recent activity</h2></div><button class="text-button" type="button" @click="screen = 'activity'; activeTab = 'more'">View all <ArrowRight :size="14" /></button></div><div class="activity-list"><div v-for="(entry, index) in activity.slice(0, 3)" :key="`${entry.title}-${index}`" class="activity-row"><span class="activity-icon" :class="`activity-${entry.kind}`"><CheckCircle2 v-if="entry.kind === 'study'" :size="16" /><Pencil v-else-if="entry.kind === 'edit'" :size="16" /><Trophy v-else-if="entry.kind === 'quiz'" :size="16" /><Plus v-else-if="entry.kind === 'create'" :size="16" /><Trash2 v-else :size="16" /></span><span class="activity-text"><strong>{{ entry.title }}</strong><small>{{ entry.detail }}</small></span><time>{{ entry.time }}</time></div></div></section>
        <p class="preview-disclaimer"><Sparkles :size="13" /> Preview data resets when the app reloads.</p>
      </section>

      <section v-else-if="screen === 'library'" class="page"><div class="page-heading"><div><p class="eyebrow">YOUR MATERIALS</p><h1>Study Library</h1><p class="muted">{{ sets.length }} saved study sets</p></div><button class="icon-button add-set" type="button" aria-label="Create study set" @click="createSet"><Plus :size="21" /></button></div><label class="search-box"><Search :size="18" /><input v-model="search" type="search" placeholder="Search your study sets..." /><Filter :size="16" /></label><div class="filter-row"><button v-for="item in (['All', 'Favorites', 'Recently Opened'] as const)" :key="item" type="button" class="filter-chip" :class="{ selected: filter === item }" @click="filter = item">{{ item }}<span v-if="item === 'All'">{{ sets.length }}</span></button></div><div class="list-caption"><span>{{ filteredSets.length }} study sets</span><button class="sort-button" type="button" @click="notify('Showing recently opened sets.')">Recently Opened <ChevronRight :size="14" /></button></div><article v-for="item in filteredSets" :key="item.id" class="set-card"><button class="set-main" type="button" @click="openSet(item.id)"><span class="subject-tile large-tile" :class="`tile-${item.color}`"><BookOpen :size="21" /></span><span class="recent-copy"><strong>{{ item.title }}</strong><span class="subject-pill">{{ item.subject }}</span><small>{{ item.questions.length }} Q <i></i> {{ item.cards.length }} F</small></span><ChevronRight class="set-chevron" :size="18" /></button><div class="set-footer"><span><Clock3 :size="13" /> {{ item.updated }}</span><div><button class="small-icon" :class="{ is-favorite: item.favorite }" type="button" aria-label="Toggle favorite" @click="toggleFavorite(item)"><Star :size="15" :fill="item.favorite ? 'currentColor' : 'none'" /></button><button class="small-icon" type="button" aria-label="Open study set" @click="openSet(item.id)"><MoreHorizontal :size="17" /></button></div></div></article><div v-if="filteredSets.length === 0" class="empty-state"><Search :size="25" /><strong>No study sets found</strong><span>Try a different search or create a new set.</span><button class="button button-primary" type="button" @click="createSet"><Plus :size="16" /> Create set</button></div></section>

      <section v-else-if="screen === 'details' && currentSet" class="page"><button class="back-link" type="button" @click="navigate('library')"><ArrowLeft :size="16" /> Study Library</button><div class="details-hero"><span class="details-cover" :class="`tile-${currentSet.color}`"><BookOpen :size="27" /></span><div><span class="subject-pill">{{ currentSet.subject }}</span><h1>{{ currentSet.title }}</h1><small>Saved on this device <i></i> {{ currentSet.updated }}</small></div><button class="small-icon is-favorite" type="button" aria-label="Toggle favorite" @click="toggleFavorite(currentSet)"><Star :size="18" :fill="currentSet.favorite ? 'currentColor' : 'none'" /></button></div><div class="detail-counts"><span><FileText :size="15" /> {{ currentSet.questions.length }} Questions</span><span><Bookmark :size="15" /> {{ currentSet.cards.length }} Flashcards</span></div><div class="segmented-tabs"><button v-for="tab in (['Overview', 'Questions', 'Flashcards'] as const)" :key="tab" type="button" :class="{ active: detailTab === tab }" @click="detailTab = tab">{{ tab }}<small v-if="tab === 'Questions'">{{ currentSet.questions.length }}</small><small v-if="tab === 'Flashcards'">{{ currentSet.cards.length }}</small></button></div><div v-if="detailTab === 'Overview'" class="detail-body"><div class="progress-summary"><div><span>Overall Progress</span><strong>{{ currentSet.cards.filter((card) => card.status === 'known').length }}/{{ currentSet.cards.length }} completed</strong></div><b>{{ Math.round(currentSet.cards.filter((card) => card.status === 'known').length / Math.max(currentSet.cards.length, 1) * 100) }}%</b><div class="progress-track"><i :style="{ width: `${currentSet.cards.filter((card) => card.status === 'known').length / Math.max(currentSet.cards.length, 1) * 100}%` }"></i></div></div><div class="info-list"><div><CalendarDays :size="18" /><span><small>Created</small><strong>Apr 12, 2025 · 2:14 PM</strong></span></div><div><Clock3 :size="18" /><span><small>Last Studied</small><strong>{{ currentSet.updated }}</strong></span></div><div><FileText :size="18" /><span><small>Original File</small><strong>{{ currentSet.title.replaceAll(' ', '_') }}.pdf</strong></span></div><div><FolderOpen :size="18" /><span><small>Folder</small><strong>{{ currentSet.subject }}</strong></span></div></div><div class="offline-banner"><CheckCircle2 :size="17" /><span><strong>Ready to study offline</strong><small>Local database saving will connect next.</small></span></div><div class="detail-buttons"><button class="button button-primary" type="button" @click="startStudy(currentSet.id)"><Play :size="15" fill="currentColor" /> Study</button><button class="button button-outline" type="button" @click="startStudy(currentSet.id)"><BookOpen :size="16" /> Flashcards</button></div><div class="detail-tools"><button type="button" @click="editSet"><Pencil :size="16" /> Edit</button><button type="button" @click="duplicateSet"><FileText :size="16" /> Duplicate</button><button type="button" @click="notify('Export will be enabled with local storage.')"><Cloud :size="16" /> Export</button><button class="delete-tool" type="button" @click="showDelete = true"><Trash2 :size="16" /> Delete</button></div></div><div v-else-if="detailTab === 'Questions'" class="detail-body"><div class="section-heading"><div><p class="eyebrow">PRACTICE</p><h2>{{ currentSet.questions.length }} Questions</h2></div><button class="text-button" type="button" @click="editQuestion()"><Plus :size="15" /> Add</button></div><button v-for="(question, index) in currentSet.questions" :key="index" class="question-row" type="button" @click="editQuestion(index)"><span>{{ String(index + 1).padStart(2, '0') }}</span><div><strong>{{ question.prompt }}</strong><small>Multiple Choice <i></i> {{ question.options.length }} options</small></div><Pencil :size="15" /></button><button class="button button-outline full-button" type="button" @click="editQuestion()"><Plus :size="16" /> Add question</button></div><div v-else class="detail-body"><div class="section-heading"><div><p class="eyebrow">ACTIVE RECALL</p><h2>{{ currentSet.cards.length }} Flashcards</h2></div><button class="text-button" type="button" @click="startStudy(currentSet.id)"><Play :size="15" /> Review</button></div><div v-for="(card, index) in currentSet.cards" :key="index" class="flashcard-row"><span class="state-dot" :class="`state-${card.status}`"></span><span><strong>{{ card.front }}</strong><small>{{ card.status === 'known' ? 'Known' : card.status === 'review' ? 'Need Review' : 'New' }}</small></span><ChevronRight :size="16" /></div></div></section>

      <section v-else-if="screen === 'edit-set'" class="page editor-page"><button class="back-link" type="button" @click="screen = currentSet ? 'details' : 'library'"><ArrowLeft :size="16" /> Cancel</button><p class="eyebrow">YOUR STUDY MATERIAL</p><h1>{{ currentSet?.title === 'New study set' ? 'Create study set' : 'Edit study set' }}</h1><label class="field-label">Study set title<input v-model="setDraft.title" maxlength="80" placeholder="e.g. Biology Chapter 4" /></label><label class="field-label">Subject<input v-model="setDraft.subject" maxlength="40" placeholder="e.g. Biology" /></label><p class="info-callout"><Sparkles :size="16" /> Questions and flashcards can be added after the set is created.</p><button class="button button-primary full-button" type="button" @click="saveSet"><Check :size="16" /> Save changes</button></section>

      <section v-else-if="screen === 'edit-question' && currentSet" class="page editor-page"><button class="back-link" type="button" @click="screen = 'details'; detailTab = 'Questions'"><ArrowLeft :size="16" /> Questions</button><p class="eyebrow">STUDY SET EDITOR</p><h1>Edit Question</h1><label class="field-label">Question type<select><option>Multiple Choice</option><option>Identification</option><option>Enumeration</option></select></label><label class="field-label">Question<textarea v-model="questionDraft.prompt" rows="3" placeholder="Write your question"></textarea></label><div class="field-label">Options <small>Tap the letter to mark the correct answer.</small></div><div v-for="(option, index) in questionDraft.options" :key="index" class="option-row"><button type="button" :class="{ correct: questionDraft.correct === index }" @click="questionDraft.correct = index">{{ String.fromCharCode(65 + index) }}</button><input v-model="questionDraft.options[index]" :aria-label="`Answer option ${index + 1}`" :placeholder="`Option ${index + 1}`" /></div><button class="text-button add-option" type="button" @click="questionDraft.options.push('')"><Plus :size="15" /> Add option</button><label class="field-label">Explanation <small>Optional</small><textarea v-model="questionDraft.explanation" rows="3" placeholder="Explain the correct answer"></textarea></label><button class="button button-primary full-button" type="button" @click="saveQuestion"><Check :size="16" /> Save question</button></section>

      <section v-else-if="screen === 'study' && currentSet" class="page study-page"><div class="study-topline"><button class="back-link" type="button" @click="navigate('flashcards')"><ArrowLeft :size="16" /> Flashcards</button><button class="icon-button" type="button" aria-label="Shuffle cards" @click="shuffleDeck"><Shuffle :size="17" /></button></div><p class="eyebrow centered">{{ currentSet.subject.toUpperCase() }}</p><h1>{{ currentSet.title }}</h1><div v-if="currentCard" class="study-count"><span>Card {{ cardIndex + 1 }} of {{ currentSet.cards.length }}</span><div class="progress-track"><i :style="{ width: `${(cardIndex + 1) / currentSet.cards.length * 100}%` }"></i></div></div><button v-if="currentCard" class="study-card" :class="{ revealed: answerRevealed }" type="button" @click="answerRevealed = !answerRevealed"><span class="face-label">{{ answerRevealed ? 'BACK' : 'FRONT' }}</span><strong>{{ answerRevealed ? currentCard.back : currentCard.front }}</strong><small><span>◉</span> Tap to {{ answerRevealed ? 'see question' : 'reveal answer' }}</small></button><div v-if="currentCard" class="review-actions"><button class="button review-again" :disabled="!answerRevealed" type="button" @click="gradeCard('review')"><span>↺</span> Review Again</button><button class="button review-known" :disabled="!answerRevealed" type="button" @click="gradeCard('known')"><Check :size="17" /> Know It</button></div><p v-if="!answerRevealed" class="review-hint">Reveal the answer before choosing.</p><div v-if="!currentCard" class="empty-state"><BookOpen :size="24" /><strong>No cards in this set yet</strong><button class="button button-outline" type="button" @click="screen = 'details'">Open study set</button></div></section>

      <section v-else-if="screen === 'quiz' && currentSet" class="page quiz-page"><div class="page-heading"><div><button class="back-link" type="button" @click="navigate('quizzes')"><ArrowLeft :size="16" /> Quizzes</button><h1>{{ currentSet.title }}</h1></div><span class="practice-label"><Timer :size="15" /> Practice</span></div><div v-if="currentQuestion" class="quiz-progress"><span>{{ quizIndex + 1 }}/{{ currentSet.questions.length }}</span><i><b :style="{ width: `${(quizIndex + 1) / currentSet.questions.length * 100}%` }"></b></i><Clock3 :size="15" /></div><div v-if="currentQuestion" class="quiz-content"><span class="question-type">Multiple Choice</span><h2>{{ currentQuestion.prompt }}</h2><div class="quiz-options"><button v-for="(option, index) in currentQuestion.options" :key="option" type="button" class="quiz-option" :class="{ chosen: chosenAnswer === index, correct: chosenAnswer !== null && index === currentQuestion.correct, incorrect: chosenAnswer === index && index !== currentQuestion.correct }" @click="chosenAnswer = index"><span>{{ String.fromCharCode(65 + index) }}</span><strong>{{ option }}</strong><Check v-if="chosenAnswer !== null && index === currentQuestion.correct" :size="16" /></button></div><div v-if="chosenAnswer !== null" class="explanation"><CheckCircle2 :size="17" /><span><strong>{{ chosenAnswer === currentQuestion.correct ? 'Correct!' : 'Review this one' }}</strong><small>{{ currentQuestion.explanation }}</small></span></div><button class="button button-primary full-button quiz-next" :disabled="chosenAnswer === null" type="button" @click="submitAnswer">{{ quizIndex === currentSet.questions.length - 1 ? 'See results' : 'Next' }} <ArrowRight :size="16" /></button></div></section>

      <section v-else-if="screen === 'results' && currentSet" class="page results-page"><button class="back-link" type="button" @click="navigate('quizzes')"><ArrowLeft :size="16" /> Quizzes</button><span class="trophy-mark"><Trophy :size="27" /></span><p class="eyebrow centered">QUIZ COMPLETE</p><h1>Nice work, Alex!</h1><p class="muted centered">Every question is a chance to learn.</p><div class="score-card"><strong>{{ quizScore }}<small> / {{ currentSet.questions.length }}</small></strong><span>correct answers</span><b>{{ currentSet.questions.length ? Math.round(quizScore / currentSet.questions.length * 100) : 0 }}% score</b></div><button class="button button-primary full-button" type="button" @click="startQuiz(currentSet.id)"><Shuffle :size="16" /> Try again</button><button class="button button-outline full-button" type="button" @click="openSet(currentSet.id); detailTab = 'Questions'">Review questions</button></section>

      <section v-else-if="screen === 'progress'" class="page"><div class="page-heading"><div><p class="eyebrow">YOUR LEARNING JOURNEY</p><h1>Flashcard Progress</h1><p class="muted">A steady rhythm beats cramming.</p></div><span class="round-icon progress-icon"><Activity :size="20" /></span></div><div class="progress-card"><div class="progress-ring"><strong>{{ totalCards ? Math.round(knownCards / totalCards * 100) : 0 }}%</strong></div><div><strong>Overall Progress</strong><small>{{ knownCards }}/{{ totalCards }} completed</small><span>This week</span></div></div><div class="status-grid"><div class="status-known"><strong>{{ knownCards }}</strong><span>Known</span></div><div class="status-review"><strong>{{ dueCards }}</strong><span>Need Review</span></div><div class="status-new"><strong>{{ totalCards - knownCards - dueCards }}</strong><span>New</span></div><div class="status-total"><strong>{{ totalCards }}</strong><span>Total</span></div></div><div class="section-heading progress-heading"><div><p class="eyebrow">UP NEXT</p><h2>Next Review</h2></div><button class="text-button" type="button" @click="startStudy()">Study now <ArrowRight :size="14" /></button></div><button v-for="item in sets.slice(0, 3)" :key="item.id" class="review-set" type="button" @click="startStudy(item.id)"><span class="subject-tile small-tile" :class="`tile-${item.color}`"><BookOpen :size="17" /></span><span><strong>{{ item.title }}</strong><small>{{ item.cards.filter((card) => card.status === 'review').length }} cards due · {{ item.subject }}</small></span><ChevronRight :size="16" /></button></section>

      <section v-else-if="screen === 'quizzes'" class="page"><div class="page-heading"><div><p class="eyebrow">CHECK YOUR UNDERSTANDING</p><h1>Quizzes</h1><p class="muted">Test what you remember.</p></div><span class="round-icon quiz-icon"><ListChecks :size="21" /></span></div><div class="quiz-banner"><Trophy :size="19" /><span><strong>Small steps count</strong><small>Practice a few questions and see what to review next.</small></span></div><div class="section-heading quiz-heading"><div><p class="eyebrow">YOUR STUDY SETS</p><h2>Start a quiz</h2></div><button class="text-button" type="button" @click="screen = 'activity'; activeTab = 'more'">History <ArrowRight :size="14" /></button></div><article v-for="item in sets" :key="item.id" class="quiz-set-card"><div class="quiz-set-main"><span class="subject-tile small-tile" :class="`tile-${item.color}`"><GraduationCap :size="19" /></span><span><small>{{ item.subject }}</small><strong>{{ item.title }}</strong></span><b>{{ item.questions.length }} Qs</b></div><div class="quiz-set-footer"><span><Timer :size="14" /> About {{ Math.max(2, Math.ceil(item.questions.length * 0.6)) }} min</span><button class="button button-primary compact-button" :disabled="!item.questions.length" type="button" @click="startQuiz(item.id)"><Play :size="13" fill="currentColor" /> Start quiz</button></div></article></section>

      <section v-else-if="screen === 'flashcards'" class="page"><div class="page-heading"><div><p class="eyebrow">ACTIVE RECALL</p><h1>Flashcards</h1><p class="muted">{{ dueCards }} cards ready for review.</p></div><span class="round-icon cards-icon"><BookOpen :size="21" /></span></div><div class="due-banner"><Clock3 :size="19" /><span><strong>{{ dueCards }} cards due today</strong><small>Review a few cards to keep them fresh.</small></span><ChevronRight :size="16" /></div><div class="section-heading deck-heading"><div><p class="eyebrow">YOUR DECKS</p><h2>Choose a study set</h2></div><button class="text-button" type="button" @click="shuffleEnabled = !shuffleEnabled"><Shuffle :size="14" /> {{ shuffleEnabled ? 'Shuffled' : 'Shuffle' }}</button></div><article v-for="item in sets" :key="item.id" class="deck-card"><button class="deck-main" type="button" @click="openSet(item.id)"><span class="subject-tile large-tile" :class="`tile-${item.color}`"><BookOpen :size="21" /></span><span><strong>{{ item.title }}</strong><small>{{ item.cards.length }} flashcards <i></i> {{ item.cards.filter((card) => card.status === 'review').length }} to review</small></span><ChevronRight :size="17" /></button><div class="deck-footer"><span class="progress-track"><i :style="{ width: `${item.cards.filter((card) => card.status === 'known').length / Math.max(item.cards.length, 1) * 100}%` }"></i></span><small>{{ item.cards.filter((card) => card.status === 'known').length }} known</small><button class="button button-primary compact-button" type="button" @click="startStudy(item.id)"><Play :size="13" fill="currentColor" /> Study</button></div></article></section>

      <section v-else-if="screen === 'more'" class="page"><div class="page-heading"><div><p class="eyebrow">YOUR SPACE</p><h1>More</h1><p class="muted">Progress, preferences, and your study data.</p></div><button class="icon-button" type="button" aria-label="Settings" @click="screen = 'settings'"><Settings :size="18" /></button></div><div class="profile-card"><span class="profile-avatar">A</span><span><strong>Alex Morgan</strong><small>Student profile · This device</small></span><ChevronRight :size="17" /></div><p class="menu-label">STUDY</p><div class="more-menu"><button type="button" @click="screen = 'progress'"><Activity :size="18" /><span><strong>Flashcard Progress</strong><small>Learned cards and review schedule</small></span><ChevronRight :size="16" /></button><button type="button" @click="screen = 'activity'"><Clock3 :size="18" /><span><strong>Activity History</strong><small>Recent edits, reviews, and quizzes</small></span><ChevronRight :size="16" /></button></div><p class="menu-label">PREFERENCES</p><div class="more-menu"><button type="button" @click="screen = 'settings'"><Settings :size="18" /><span><strong>Settings</strong><small>App preferences and storage</small></span><ChevronRight :size="16" /></button><button type="button" @click="notify('Backup will connect after SQLite is implemented.')"><Cloud :size="18" /><span><strong>Backup & Export</strong><small>Protect your study materials</small></span><ChevronRight :size="16" /></button><button type="button" @click="pickUpload"><Upload :size="18" /><span><strong>Import</strong><small>Load study sets from a file</small></span><ChevronRight :size="16" /></button><button type="button" @click="notify('Help and privacy information will be added before release.')"><GraduationCap :size="18" /><span><strong>Help & Support</strong><small>FAQs and app information</small></span><ChevronRight :size="16" /></button></div><p class="version-label">Studyday · Preview 0.1</p></section>

      <section v-else-if="screen === 'settings'" class="page"><button class="back-link" type="button" @click="navigate('more')"><ArrowLeft :size="16" /> More</button><p class="eyebrow">PREFERENCES</p><h1>Settings</h1><p class="muted">Make Studyday work for your routine.</p><div class="settings-list"><label class="setting-row"><span><Bell :size="18" /><span><strong>Study reminders</strong><small>Gentle reminders on study days</small></span></span><input v-model="remindersEnabled" type="checkbox" /></label><label class="setting-row"><span><Shuffle :size="18" /><span><strong>Shuffle flashcards</strong><small>Mix the deck when studying</small></span></span><input v-model="shuffleEnabled" type="checkbox" /></label><button class="setting-row" type="button" @click="notify('Local database setup is next.')"><span><Cloud :size="18" /><span><strong>Device storage</strong><small>Available offline after saving is connected</small></span></span><ChevronRight :size="16" /></button></div><button class="button button-outline full-button" type="button" @click="notify('Preview preferences active for this session.')"><Check :size="16" /> Save preferences</button></section>

      <section v-else-if="screen === 'activity'" class="page"><button class="back-link" type="button" @click="navigate('more')"><ArrowLeft :size="16" /> More</button><div class="page-heading"><div><p class="eyebrow">YOUR LEARNING JOURNEY</p><h1>Recent Activity</h1><p class="muted">A timeline of your edits and study sessions.</p></div><span class="round-icon activity-heading-icon"><Activity :size="20" /></span></div><div class="activity-list full-activity"> <div v-for="(entry, index) in activity" :key="`${entry.title}-${index}`" class="activity-row"><span class="activity-icon" :class="`activity-${entry.kind}`"><CheckCircle2 v-if="entry.kind === 'study'" :size="16" /><Pencil v-else-if="entry.kind === 'edit'" :size="16" /><Trophy v-else-if="entry.kind === 'quiz'" :size="16" /><Plus v-else-if="entry.kind === 'create'" :size="16" /><Trash2 v-else :size="16" /></span><span class="activity-text"><strong>{{ entry.title }}</strong><small>{{ entry.detail }}</small></span><time>{{ entry.time }}</time></div></div><p class="preview-disclaimer"><Sparkles :size="13" /> Activity is preview data and clears when the app reloads.</p></section>
    </main>

    <nav v-if="showBottomNav" class="bottom-nav" aria-label="Main navigation"><button v-for="item in navItems" :key="item.id" type="button" :class="{ active: activeTab === item.id }" :aria-current="activeTab === item.id ? 'page' : undefined" @click="navigate(item.id)"><component :is="item.icon" :size="19" /><span>{{ item.label }}</span></button></nav>
    <input ref="fileInput" class="visually-hidden" type="file" accept="application/pdf,image/*" @change="fileSelected" />
    <div v-if="showDelete" class="modal-backdrop" @click.self="showDelete = false"><section class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="delete-title"><button class="modal-close" type="button" aria-label="Close" @click="showDelete = false"><X :size="18" /></button><span class="delete-mark"><Trash2 :size="20" /></span><h2 id="delete-title">Delete this study set?</h2><p>This removes the set from the current preview. You can’t undo this action.</p><div><button class="button button-outline" type="button" @click="showDelete = false">Keep set</button><button class="button button-danger" type="button" @click="deleteSet">Delete set</button></div></section></div>
    <Transition name="toast"><div v-if="toast" class="toast-message" role="status"><CheckCircle2 :size="17" /> {{ toast }}</div></Transition>
  </div>
</template>
