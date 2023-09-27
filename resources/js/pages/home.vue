<template>
  <div class="w-full h-full">
    <div class="rounded bg-white shadow p-8">
      <div class="w-full mb-3 flex">
        <div class="w-auto">
          <label class="font-bold">Per Page</label>
          <select class="border rounded py-2 px-3 text-base cursor-pointer" v-model="perpage" @change="pager()">
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
        </div>
      </div>
      <div class="table-container border border-gray-300">
        <table class="table">
          <thead>
            <th>No</th>
            <th>Name</th>
            <th>CH Name</th>
            <th>Price</th>
            <th>Discount</th>
            <th>Special</th>
            <th>Priority</th>
            <th>Desciption</th>
            <th>Show</th>
            <th>Created At</th>
            <th>Created By</th>
            <th>Updated At</th>
            <th>Updated By</th>
          </thead>
          <tbody>
            <tr v-for="(item, index) in items" :key="index">
              <td>{{ parseFloat(index) + 1 }}</td>
              <td>{{ item.name }}</td>
              <td>{{ item.ch_name }}</td>
              <td>{{ item.price }}</td>
              <td>{{ item.discount }}</td>
              <td>{{ item.isSpecial }}</td>
              <td>{{ item.priority }}</td>
              <td>{{ item.description }}</td>
              <td>{{ item.isShow }}</td>
              <td>{{ item.created_at }}</td>
              <td>{{ item.created_by || "-" }}</td>
              <td>{{ item.updated_at }}</td>
              <td>{{ item.updated_by || "-" }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="w-full mt-2">
        <button
          :class="
            n === currentActive
              ? 'btn-paginate bg-teal-500 text-white'
              : 'btn-paginate'
          "
          @click="page(n)"
          v-for="n in paginates"
        >
          {{ n }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  //middleware: 'auth',

  metaInfo() {
    return { title: this.$t("home") };
  },

  data: () => ({
    items: [],
    paginates: 1,
    currentActive: 1,
    perpage: 25
  }),

  methods: {
    retrieves() {
      const data = window.localStorage.getItem("excelData");
      const rows = JSON.parse(data);

      return rows;
    },

    first() {
      const rows = this.retrieves();
      this.items = rows[0];
      this.paginates = rows.length;
    },

    page(number, replace = true) {
      const rows = this.retrieves();

      this.items = rows[number - 1];
      this.currentActive = number;
      this.paginates = rows.length;

      if (replace) {
        this.$router.push({ name: "dashboard.home", query: { page: number } });
      }
    },

    clear() {
      window.localStorage.removeItem("excelData");
    },

    async pager() {
      this.clear();
      window.localStorage.setItem('perpage', this.perpage);

      const loaded = await this.load();

      if(loaded) {
        const rows = this.retrieves();
        this.items = rows[0];
        this.currentActive = 1;
        this.paginates = rows.length;

        const hasPage = this.$route.query.page;
        if (hasPage) {
          this.$router.push({ name: "dashboard.home"});
        }
      }
    },
    
    async load() {
      const { data } = await axios.post("/api/load-data?perpage=" + this.perpage);

      if (data.status) {
        const rows = JSON.stringify(data.data);
        window.localStorage.setItem("excelData", rows);
      }

      return true;
    },
  },

  async mounted() {
    const retrieve = window.localStorage.getItem("excelData");
    const page = window.localStorage.getItem("perpage");

    if(page) {
      this.perpage = parseFloat(page)
    }

    if (!retrieve) await this.load();

    const hasPage = this.$route.query.page;
    if (hasPage) return this.page(parseFloat(hasPage), false);

    return this.first();
  },
};
</script>
