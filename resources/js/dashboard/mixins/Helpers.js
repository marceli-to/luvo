export default {

  methods: {
    
    /**
     * Build a string out of an array of tutors
     * 
     * @param object data
     * @return string tutors
     */
    tutorsToString(data) {
      // filter out names, remove duplicates and create string
      return [...new Set([...data.map(x => x.tutor.name)])].join('/');
    },

    /**
     * Build a string out of an array of dates
     * 
     * @param object data
     * @return string dates
     */
    datesToString(data, format = 'DD.MM.YY', appendYear = false) {
      // filter out dates, format and create string
      let str = [...data.map(x => x.date)].join('/');
      return !appendYear ? str : str + moment(data[0].date).format('Y');
    },

    moneyFormat(amount) {
      return amount.toFixed(2);
    },

    shorten(str, length = 10, suffix = "...") {
      return str.substring(0, length) + suffix;
    },

    randomString() {
      return Math.random().toString(36).slice(2);
    },
  }
};