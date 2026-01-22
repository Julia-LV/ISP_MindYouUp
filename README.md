# Tic Tracker

A comprehensive web application designed to help patients track their tics, emotional well-being, and daily health metrics. This system allows users to log data and visualize patterns through interactive charts, facilitating better health management and communication with healthcare professionals.

## Features

- **Interactive Dashboard**
  - Visualization of Tic Frequency and Intensity.
  - Correlation analysis between Stress and Tic Severity.
  - Daily Rhythm charts showing activity throughout the day.
  - Affected Muscle Group distribution.

- **Emotional Diary**
  - Track Stress, Anxiety, and Sleep quality levels (0-10).
  - View weekly trends and averages.
  - "Recent Activity" logs for quick reference.

- **Health Tracking**
  - **Tic Log:** Record Motor and Vocal tics with intensity ratings.
  - **Medication Tracker:** View pending medications and reminders.
  


## 🛠️ Technologies Used

- **Frontend:** HTML5, Tailwind CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Libraries:**
  - [Chart.js](https://www.chartjs.org/) (Data Visualization)
  - [Lucide Icons](https://lucide.dev/) (UI Icons)
  - Google Translate API (Multi-language support)

## 📂 Project Structure

- `/pages/patient/` - Main patient interface files (Home, Tic Log, Diary).
- `/components/` - Reusable UI components (Header, Push Notifications).
- `/js/` - Logic for charts (`home_patient.js`, `emotional_diary_visuals.js`) and navigation (`navbar.js`).
- `/css/` - Custom styling and Tailwind utilities.

