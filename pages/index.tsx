// pages/index.tsx
import type { NextPage } from 'next';
import NotesList from '../components/NotesList';

const Home: NextPage = () => {
  return (
    <div>
      <h1>Sticky Notes</h1>
      <NotesList />
    </div>
  );
};

export default Home;
