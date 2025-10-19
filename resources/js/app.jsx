import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import PillNav from './components/navbar';

const navContainer = document.getElementById('react-navbar');

if (navContainer) {
  const userData = navContainer.dataset.user ? JSON.parse(navContainer.dataset.user) : null;
  const currentPage = navContainer.dataset.currentPage || '';
  const usersData = navContainer.dataset.users ? JSON.parse(navContainer.dataset.users) : [];
  
  const root = ReactDOM.createRoot(navContainer);
  root.render(
    <BrowserRouter>
      <PillNav 
        activeHref={`/${currentPage}`}
        user={userData}
        admin={userData?.admin || false}
        users={usersData}
      />
    </BrowserRouter>
  );
}