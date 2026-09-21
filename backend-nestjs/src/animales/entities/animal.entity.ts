
import { Entity, PrimaryGeneratedColumn, Column, CreateDateColumn, UpdateDateColumn } from 'typeorm';

@Entity('animales')
export class Animal {
  @PrimaryGeneratedColumn({ name: 'id_animal' })
  idAnimal: number;

  @Column({ name: 'numero_arete' })
  numeroArete: string;

  @Column({ name: 'raza_id' })
  razaId: number;

  @Column()
  sexo: string;

  @Column({ name: 'fecha_nacimiento', type: 'date' })
  fechaNacimiento: string;

  @Column()
  estado: string;

  @Column({ name: 'potrero_id' })
  potreroId: number;

  @CreateDateColumn({ name: 'created_at' })
  createdAt: Date;

  @UpdateDateColumn({ name: 'updated_at' })
  updatedAt: Date;
}