import './bootstrap';
import store from './store.js';
import { createApp } from 'vue';
import { createStore } from 'vuex';


import Floor from './components/backend/Floor.vue';
import Room from './components/backend/Room.vue';
import NoticeComponent from './components/frontend/Notice.vue';
import GalleryComponent from './components/frontend/Gallery.vue';
import ResidenceOverviewComponent from './components/frontend/ResidenceOverview.vue';
import RoomBookingHistory from './components/backend/RoomBookingHistory.vue';
import TodayRoomBookingHistory from './components/backend/TodayRoomBookingHistory.vue';
import Staffs from './components/backend/Staffs.vue';
import StaffsAttendance from './components/backend/StaffsAttendance.vue';
import StaffSalary from './components/backend/StaffSalary.vue';
import Expense from './components/backend/Expense.vue';
import ExpenseType from './components/backend/ExpenseType.vue';
import Supplier from './components/backend/Supplier.vue';
import Product from './components/backend/Product.vue';
import Brand from './components/backend/Brand.vue';
import BrandCategory from './components/backend/BrandCategory.vue';
import ProductStock from './components/backend/ProductStock.vue';
import ProductDistribution from './components/backend/ProductDistribution.vue';
import Report from './components/backend/Report.vue';
import CustomerReport from './components/backend/CustomerReport.vue';
import Management from './components/backend/Management.vue';
import MonthlyPaymentList from './components/backend/MonthlyPayment.vue';
import ResidentReleaseManager from './components/backend/ResidentReleaseManager.vue';
import ResidentReleaseHistory from './components/backend/ResidentReleaseHistory.vue';

const app = createApp({
    components:{
        NoticeComponent,
        GalleryComponent,
        ResidenceOverviewComponent,
        Room,
        Floor,
        RoomBookingHistory,
        TodayRoomBookingHistory,
        Staffs,
        StaffsAttendance,
        StaffSalary,
        Expense,
        ExpenseType,
        Supplier,
        Product,
        Brand,
        BrandCategory,
        ProductStock,
        ProductDistribution,
        Report,
        CustomerReport,
        Management,
        MonthlyPaymentList,
        ResidentReleaseManager,
        ResidentReleaseHistory,
    }
});

app.use(store);
app.mount('#app');
