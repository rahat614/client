import React, {createContext, useContext, useEffect, useReducer, ReactNode} from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import {Medicine, StockAlert, StockTransaction, AlertType, TransactionType} from '../types/Medicine';

interface MedicineState {
  medicines: Medicine[];
  alerts: StockAlert[];
  transactions: StockTransaction[];
  loading: boolean;
  error: string | null;
}

type MedicineAction =
  | {type: 'SET_LOADING'; payload: boolean}
  | {type: 'SET_ERROR'; payload: string | null}
  | {type: 'SET_MEDICINES'; payload: Medicine[]}
  | {type: 'ADD_MEDICINE'; payload: Medicine}
  | {type: 'UPDATE_MEDICINE'; payload: Medicine}
  | {type: 'DELETE_MEDICINE'; payload: string}
  | {type: 'SET_ALERTS'; payload: StockAlert[]}
  | {type: 'ADD_ALERT'; payload: StockAlert}
  | {type: 'MARK_ALERT_READ'; payload: string}
  | {type: 'SET_TRANSACTIONS'; payload: StockTransaction[]}
  | {type: 'ADD_TRANSACTION'; payload: StockTransaction};

const initialState: MedicineState = {
  medicines: [],
  alerts: [],
  transactions: [],
  loading: false,
  error: null,
};

const medicineReducer = (state: MedicineState, action: MedicineAction): MedicineState => {
  switch (action.type) {
    case 'SET_LOADING':
      return {...state, loading: action.payload};
    case 'SET_ERROR':
      return {...state, error: action.payload};
    case 'SET_MEDICINES':
      return {...state, medicines: action.payload};
    case 'ADD_MEDICINE':
      return {...state, medicines: [...state.medicines, action.payload]};
    case 'UPDATE_MEDICINE':
      return {
        ...state,
        medicines: state.medicines.map(med =>
          med.id === action.payload.id ? action.payload : med
        ),
      };
    case 'DELETE_MEDICINE':
      return {
        ...state,
        medicines: state.medicines.filter(med => med.id !== action.payload),
      };
    case 'SET_ALERTS':
      return {...state, alerts: action.payload};
    case 'ADD_ALERT':
      return {...state, alerts: [...state.alerts, action.payload]};
    case 'MARK_ALERT_READ':
      return {
        ...state,
        alerts: state.alerts.map(alert =>
          alert.id === action.payload ? {...alert, isRead: true} : alert
        ),
      };
    case 'SET_TRANSACTIONS':
      return {...state, transactions: action.payload};
    case 'ADD_TRANSACTION':
      return {...state, transactions: [...state.transactions, action.payload]};
    default:
      return state;
  }
};

interface MedicineContextType {
  state: MedicineState;
  addMedicine: (medicine: Omit<Medicine, 'id' | 'createdAt' | 'updatedAt'>) => Promise<void>;
  updateMedicine: (medicine: Medicine) => Promise<void>;
  deleteMedicine: (id: string) => Promise<void>;
  addTransaction: (transaction: Omit<StockTransaction, 'id' | 'timestamp'>) => Promise<void>;
  markAlertAsRead: (alertId: string) => void;
  checkForAlerts: () => void;
  searchMedicines: (query: string) => Medicine[];
  getLowStockMedicines: () => Medicine[];
  getExpiringMedicines: (days: number) => Medicine[];
}

const MedicineContext = createContext<MedicineContextType | undefined>(undefined);

const STORAGE_KEYS = {
  MEDICINES: '@medicines',
  ALERTS: '@alerts',
  TRANSACTIONS: '@transactions',
};

export const MedicineProvider: React.FC<{children: ReactNode}> = ({children}) => {
  const [state, dispatch] = useReducer(medicineReducer, initialState);

  // Load data from AsyncStorage on app start
  useEffect(() => {
    loadData();
  }, []);

  // Save data to AsyncStorage whenever state changes
  useEffect(() => {
    if (state.medicines.length > 0) {
      saveData();
    }
  }, [state.medicines, state.alerts, state.transactions]);

  const loadData = async () => {
    try {
      dispatch({type: 'SET_LOADING', payload: true});
      
      const [medicinesData, alertsData, transactionsData] = await Promise.all([
        AsyncStorage.getItem(STORAGE_KEYS.MEDICINES),
        AsyncStorage.getItem(STORAGE_KEYS.ALERTS),
        AsyncStorage.getItem(STORAGE_KEYS.TRANSACTIONS),
      ]);

      if (medicinesData) {
        const medicines = JSON.parse(medicinesData).map((med: any) => ({
          ...med,
          expiryDate: new Date(med.expiryDate),
          createdAt: new Date(med.createdAt),
          updatedAt: new Date(med.updatedAt),
        }));
        dispatch({type: 'SET_MEDICINES', payload: medicines});
      }

      if (alertsData) {
        const alerts = JSON.parse(alertsData).map((alert: any) => ({
          ...alert,
          createdAt: new Date(alert.createdAt),
        }));
        dispatch({type: 'SET_ALERTS', payload: alerts});
      }

      if (transactionsData) {
        const transactions = JSON.parse(transactionsData).map((transaction: any) => ({
          ...transaction,
          timestamp: new Date(transaction.timestamp),
        }));
        dispatch({type: 'SET_TRANSACTIONS', payload: transactions});
      }
    } catch (error) {
      dispatch({type: 'SET_ERROR', payload: 'Failed to load data'});
    } finally {
      dispatch({type: 'SET_LOADING', payload: false});
    }
  };

  const saveData = async () => {
    try {
      await Promise.all([
        AsyncStorage.setItem(STORAGE_KEYS.MEDICINES, JSON.stringify(state.medicines)),
        AsyncStorage.setItem(STORAGE_KEYS.ALERTS, JSON.stringify(state.alerts)),
        AsyncStorage.setItem(STORAGE_KEYS.TRANSACTIONS, JSON.stringify(state.transactions)),
      ]);
    } catch (error) {
      dispatch({type: 'SET_ERROR', payload: 'Failed to save data'});
    }
  };

  const generateId = (): string => {
    return Date.now().toString() + Math.random().toString(36).substr(2, 9);
  };

  const addMedicine = async (medicineData: Omit<Medicine, 'id' | 'createdAt' | 'updatedAt'>) => {
    const medicine: Medicine = {
      ...medicineData,
      id: generateId(),
      createdAt: new Date(),
      updatedAt: new Date(),
    };

    dispatch({type: 'ADD_MEDICINE', payload: medicine});

    // Add initial stock transaction
    const transaction: StockTransaction = {
      id: generateId(),
      medicineId: medicine.id,
      type: TransactionType.STOCK_IN,
      quantity: medicine.quantity,
      reason: 'Initial stock',
      timestamp: new Date(),
    };
    dispatch({type: 'ADD_TRANSACTION', payload: transaction});
  };

  const updateMedicine = async (medicine: Medicine) => {
    const updatedMedicine = {
      ...medicine,
      updatedAt: new Date(),
    };
    dispatch({type: 'UPDATE_MEDICINE', payload: updatedMedicine});
  };

  const deleteMedicine = async (id: string) => {
    dispatch({type: 'DELETE_MEDICINE', payload: id});
  };

  const addTransaction = async (transactionData: Omit<StockTransaction, 'id' | 'timestamp'>) => {
    const transaction: StockTransaction = {
      ...transactionData,
      id: generateId(),
      timestamp: new Date(),
    };

    dispatch({type: 'ADD_TRANSACTION', payload: transaction});

    // Update medicine quantity
    const medicine = state.medicines.find(med => med.id === transaction.medicineId);
    if (medicine) {
      let newQuantity = medicine.quantity;
      if (transaction.type === TransactionType.STOCK_IN) {
        newQuantity += transaction.quantity;
      } else if (transaction.type === TransactionType.STOCK_OUT || transaction.type === TransactionType.EXPIRED_REMOVAL) {
        newQuantity -= transaction.quantity;
      } else if (transaction.type === TransactionType.ADJUSTMENT) {
        newQuantity = transaction.quantity;
      }

      await updateMedicine({
        ...medicine,
        quantity: Math.max(0, newQuantity),
      });
    }
  };

  const markAlertAsRead = (alertId: string) => {
    dispatch({type: 'MARK_ALERT_READ', payload: alertId});
  };

  const checkForAlerts = () => {
    const newAlerts: StockAlert[] = [];
    const now = new Date();
    const thirtyDaysFromNow = new Date(now.getTime() + 30 * 24 * 60 * 60 * 1000);

    state.medicines.forEach(medicine => {
      // Check for low stock
      if (medicine.quantity <= medicine.minStockLevel) {
        newAlerts.push({
          id: generateId(),
          medicineId: medicine.id,
          medicineName: medicine.name,
          alertType: AlertType.LOW_STOCK,
          message: `${medicine.name} is running low (${medicine.quantity} remaining)`,
          createdAt: new Date(),
          isRead: false,
        });
      }

      // Check for expired medicines
      if (medicine.expiryDate < now) {
        newAlerts.push({
          id: generateId(),
          medicineId: medicine.id,
          medicineName: medicine.name,
          alertType: AlertType.EXPIRED,
          message: `${medicine.name} has expired`,
          createdAt: new Date(),
          isRead: false,
        });
      }

      // Check for medicines expiring soon
      if (medicine.expiryDate > now && medicine.expiryDate < thirtyDaysFromNow) {
        newAlerts.push({
          id: generateId(),
          medicineId: medicine.id,
          medicineName: medicine.name,
          alertType: AlertType.EXPIRING_SOON,
          message: `${medicine.name} expires on ${medicine.expiryDate.toDateString()}`,
          createdAt: new Date(),
          isRead: false,
        });
      }
    });

    newAlerts.forEach(alert => dispatch({type: 'ADD_ALERT', payload: alert}));
  };

  const searchMedicines = (query: string): Medicine[] => {
    if (!query.trim()) return state.medicines;
    
    const lowercaseQuery = query.toLowerCase();
    return state.medicines.filter(medicine =>
      medicine.name.toLowerCase().includes(lowercaseQuery) ||
      medicine.genericName?.toLowerCase().includes(lowercaseQuery) ||
      medicine.manufacturer.toLowerCase().includes(lowercaseQuery) ||
      medicine.category.toLowerCase().includes(lowercaseQuery)
    );
  };

  const getLowStockMedicines = (): Medicine[] => {
    return state.medicines.filter(medicine => medicine.quantity <= medicine.minStockLevel);
  };

  const getExpiringMedicines = (days: number): Medicine[] => {
    const futureDate = new Date();
    futureDate.setDate(futureDate.getDate() + days);
    
    return state.medicines.filter(medicine => 
      medicine.expiryDate <= futureDate && medicine.expiryDate >= new Date()
    );
  };

  return (
    <MedicineContext.Provider
      value={{
        state,
        addMedicine,
        updateMedicine,
        deleteMedicine,
        addTransaction,
        markAlertAsRead,
        checkForAlerts,
        searchMedicines,
        getLowStockMedicines,
        getExpiringMedicines,
      }}>
      {children}
    </MedicineContext.Provider>
  );
};

export const useMedicine = (): MedicineContextType => {
  const context = useContext(MedicineContext);
  if (context === undefined) {
    throw new Error('useMedicine must be used within a MedicineProvider');
  }
  return context;
};