# Business Context & Application Understanding

## Executive Summary
The **FLC UMJ Gamified LMS** is a specialized learning management system integrated with a gamification engine, tailored for the Faculty of Language and Communication at Universitas Muhammadiyah Jakarta (FLC UMJ). Its primary objective is to elevate student engagement and completion rates in coursework by introducing gamified mechanics such as Experience Points (XP), levels, badges, and a public leaderboard (PBL framework).

Currently, the application is at a stage where it contains fully functional core features for both students (dashboard, material viewing, task submission, leaderboard) and admins (material/task catalog CRUD and grading panel). While functional, the system operates as a single-instance monolithic app, requiring substantial modernization to scale to an enterprise level.

---

## Business Goals
1. **Enhance Student Engagement:** Shift student learning behavior from passive reading to active participation using Points, Badges, and Leaderboards (PBL).
2. **Improve Course Completion Rates:** Incentivize structured completion of learning materials and assignments through progressive levels and rewards.
3. **Streamline Administration:** Provide lecturers and administrators with a fast, responsive grading tool (Grading Station) that automates reward calculations.
4. **Academically Showcase Gamification:** Serve as a working implementation of gamification in Higher Education for academic research and validation.

---

## User Personas

### 1. The Student (Member)
* **Demographics:** Language and communication undergraduate students at UMJ.
* **Goals:** Consume learning resources, complete tasks, earn XP to level up, and unlock achievements to compete on the leaderboard.
* **Pain Points:** Traditional LMSs feel dry, lack clear progress indicators, and offer no instant feedback or recognition for diligence.

### 2. The Lecturer / Grader (Admin)
* **Demographics:** Teaching faculty members.
* **Goals:** Upload course materials, assign coursework with deadlines, grade submissions efficiently, and reward student progress.
* **Pain Points:** Grading is time-consuming; manually calculating progress rewards for large classes is administrative overhead.

---

## Stakeholders
* **Faculty Leadership (Deans/Department Heads):** Interested in macro metrics such as overall student performance, engagement levels, and course success.
* **University IT Department:** Concerned with hosting, server performance, database integrity, integration with existing campus systems, and security compliance.
* **Lecturers & Instructors:** Direct administrators of course content and grading.
* **Students:** Primary users who interact with the system daily.

---

## Current Features
* **Gamified Student Dashboard:** Centralized hub featuring profile header, XP progress bar, unlocked badges grid, mini leaderboard, upcoming tasks list, and recent XP logs feed.
* **Material Viewer:** Interface that lets students read documents, watch videos, or open external links, automatically awarding `+10 XP` on first-time reading.
* **Task Submission System:** Supports essay writing and file uploads, blocking multiple submissions to prevent spam.
* **Hall of Fame (Leaderboard):** Interactive top-50 ranking list sorted by total XP, highlighting the active user's rank with optimal performance queries.
* **Admin Grading Station:** Eager-loaded submission queue list with sidebar navigation, enabling admins to grade essays or files (0-100 score) and atomically distribute proportional XP.
* **Admin Materials & Tasks CRUD:** Livewire managers with paginated lists, form validation, and modal panels.

---

## Missing Features (Gaps)
1. **Academic System Integration (SIAKAD):** No single-sign-on (SSO) or syncing of grades/enrollments with the UMJ central student database.
2. **Notification Center:** No mechanism (email, push, browser) to notify students when tasks are graded or badges unlocked.
3. **Interactive Quiz Engine:** Although task types include `quiz`, the interface and processing for interactive quizzes are missing.
4. **Audit Trail & Activity Logging:** No system-level audit logs tracking admin actions (e.g., deleted materials, altered grades).

---

## Recommended Future Features
* **SSO Authentication (SAML2/OIDC):** Implement Single Sign-On so users can log in using their official UMJ campus credentials.
* **Automated Quiz Evaluator:** Create a module that auto-evaluates multiple-choice quizzes and instantly grants XP, bypassing the admin grading bottleneck.
* **Event Notifications (Slack/Email/Web):** Send real-time updates when tasks are nearing deadlines, grades are published, or a student gets overtaken on the leaderboard.
* **Advanced Analytics Dashboard:** Provide lecturers with analytics on which materials are most viewed and which tasks students struggle with.
