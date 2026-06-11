import { createI18n } from 'vue-i18n'

const messages = {
  en: {
    nav: {
      home: 'Home',
      calls: 'Research Calls',
      events: 'Events',
      publications: 'Publications',
      researchers: 'Researchers',
      community: 'Community',
      dashboard: 'Dashboard',
      signOut: 'Sign Out',
      signIn: 'Sign In',
      signUp: 'Sign Up',
      goToDashboard: 'Go to Dashboard',
      about: 'About'
    },
    home: {
      heroBadge: 'Transforming Higher Education',
      heroTitle: 'The Central Hub for',
      heroTitleHighlight: 'Academic Research',
      heroSubtitle: 'Discover ground-breaking projects, browse the latest publications, explore open research calls, and track our ongoing community impact across universities.',
      exploreCalls: 'Explore Open Calls',
      browsePubs: 'Browse Publications',
      stats: {
        universities: 'Universities',
        openCalls: 'Open Calls',
        publications: 'Publications',
        communityImpact: 'Problems Solved'
      },
      openCallsTitle: 'Open Research Calls',
      openCallsSubtitle: 'Opportunities for call for proposal and collaboration',
      viewAll: 'View all',
      noOpenCalls: 'No open calls',
      noOpenCallsMessage: 'There are currently no open calls. Check back later.',
      deadline: 'Deadline',
      central: 'Central'
    }
  },
  am: {
    nav: {
      home: 'መነሻ',
      calls: 'የምርምር ጥሪዎች',
      events: 'ክስተቶች',
      publications: 'ህትመቶች',
      researchers: 'ተመራማሪዎች',
      community: 'ማህበረሰብ',
      dashboard: 'ዳሽቦርድ',
      signOut: 'ውጣ',
      signIn: 'ግባ',
      signUp: 'ተመዝገብ',
      goToDashboard: 'ወደ ዳሽቦርድ ሂድ',
      about: 'ስለ'
    },
    home: {
      heroBadge: 'ከፍተኛ ትምህርትን መለወጥ',
      heroTitle: 'ማዕከላዊ መድረክ ለ',
      heroTitleHighlight: 'አካዳሚክ ምርምር',
      heroSubtitle: 'አዳዲስ ፕሮጀክቶችን ያግኙ፣ የቅርብ ጊዜ ህትመቶችን ያስሱ፣ ክፍት የምርምር ጥሪዎችን ይመልከቱ እና በዩኒቨርሲቲዎች ያለንን የማህበረሰብ ተጽዕኖ ይከታተሉ።',
      exploreCalls: 'ክፍት ጥሪዎችን ያስሱ',
      browsePubs: 'ህትመቶችን ይመልከቱ',
      stats: {
        universities: 'ዩኒቨርሲቲዎች',
        openCalls: 'ክፍት ጥሪዎች',
        publications: 'ህትመቶች',
        communityImpact: 'የተፈቱ ችግሮች'
      },
      openCallsTitle: 'ክፍት የምርምር ጥሪዎች',
      openCallsSubtitle: 'የገንዘብ ድጋፍ እና ትብብር እድሎች',
      viewAll: 'ሁሉንም ይመልከቱ',
      noOpenCalls: 'ክፍት ጥሪዎች የሉም',
      noOpenCallsMessage: 'በአሁኑ ጊዜ ምንም ክፍት ጥሪዎች የሉም። እባክዎ ቆየት ብለው ይሞክሩ።',
      deadline: 'የማብቂያ ጊዜ',
      central: 'ማዕከላዊ'
    }
  }
}

const currentLang = localStorage.getItem('language') || 'en'

const i18n = createI18n({
  legacy: false, // Enable Composition API mode
  locale: currentLang, // set locale
  fallbackLocale: 'en', // set fallback locale
  messages, // set locale messages
})

export default i18n
