// redux/store.ts
import { configureStore } from '@reduxjs/toolkit';
import { useDispatch } from 'react-redux';
import { notesReducer } from './notesSlice'; // Correctly import notesReducer

const store = configureStore({
  reducer: {
    notes: notesReducer, // Use the imported notesReducer
  },
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;
export const useAppDispatch = () => useDispatch<AppDispatch>();

export default store;
